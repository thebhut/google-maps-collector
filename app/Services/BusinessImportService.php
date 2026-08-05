<?php

namespace App\Services;

use App\Models\Business;
use App\Models\CollectionError;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessImportService
{
    public function __construct(protected BusinessDataNormalizer $normalizer)
    {
    }

    /**
     * Import a batch of business records for a user.
     */
    public function importBatch(User $user, array $businesses): array
    {
        $now = Carbon::now();
        $received = count($businesses);
        $inserted = 0;
        $updated = 0;
        $duplicates = 0;
        $failed = 0;

        DB::beginTransaction();
        try {
            foreach ($businesses as $rawBusiness) {
                if (empty($rawBusiness['name'])) {
                    $failed++;
                    $this->logError($user, 'INVALID_RECORD', 'Business name is missing', $rawBusiness);
                    continue;
                }

                $normalized = $this->normalizer->normalize($rawBusiness);
                $normalized['user_id'] = $user->id;

                $existing = $this->findDuplicate($user->id, $normalized);

                if ($existing) {
                    $duplicates++;
                    $updated++;

                    $updateData = array_filter([
                        'phone' => $normalized['phone'] ?? $existing->phone,
                        'email' => $normalized['email'] ?? $existing->email,
                        'address' => $normalized['address'] ?? $existing->address,
                        'city' => $normalized['city'] ?? $existing->city,
                        'state' => $normalized['state'] ?? $existing->state,
                        'country' => $normalized['country'] ?? $existing->country,
                        'postal_code' => $normalized['postal_code'] ?? $existing->postal_code,
                        'website' => $normalized['website'] ?? $existing->website,
                        'category' => $normalized['category'] ?? $existing->category,
                        'rating' => $normalized['rating'] ?? $existing->rating,
                        'review_count' => $normalized['review_count'] ?? $existing->review_count,
                        'maps_url' => $normalized['maps_url'] ?? $existing->maps_url,
                        'latitude' => $normalized['latitude'] ?? $existing->latitude,
                        'longitude' => $normalized['longitude'] ?? $existing->longitude,
                        'last_collected_at' => $now,
                    ], fn ($val) => !is_null($val));

                    $existing->update($updateData);
                } else {
                    $inserted++;
                    $normalized['first_collected_at'] = $now;
                    $normalized['last_collected_at'] = $now;
                    Business::create($normalized);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Business import batch failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->logError($user, 'BATCH_IMPORT_ERROR', $e->getMessage());
            throw $e;
        }

        return [
            'received' => $received,
            'inserted' => $inserted,
            'updated' => $updated,
            'collected' => ($inserted + $updated),
            'duplicates' => 0, // Successfully inserted or updated records are collected leads!
            'failed' => $failed,
        ];
    }

    protected function findDuplicate(int $userId, array $normalized): ?Business
    {
        if (!empty($normalized['place_id'])) {
            $byPlaceId = Business::where('user_id', $userId)
                ->where('place_id', $normalized['place_id'])
                ->first();

            if ($byPlaceId) {
                return $byPlaceId;
            }
        }

        $name = $normalized['name'];
        $query = Business::where('user_id', $userId)->where('name', $name);

        if (!empty($normalized['phone'])) {
            $byPhone = (clone $query)->where('phone', $normalized['phone'])->first();
            if ($byPhone) {
                return $byPhone;
            }
        }

        if (!empty($normalized['address'])) {
            $byAddress = (clone $query)->where('address', $normalized['address'])->first();
            if ($byAddress) {
                return $byAddress;
            }
        }

        return null;
    }

    protected function logError(User $user, string $errorType, string $message, ?array $payload = null): void
    {
        CollectionError::create([
            'user_id' => $user->id,
            'error_type' => $errorType,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
