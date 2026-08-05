<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\BusinessImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessApiController extends Controller
{
    public function __construct(protected BusinessImportService $importService)
    {
    }

    /**
     * POST /api/v1/businesses/bulk
     * Bulk upload business records from Chrome Extension.
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'businesses' => 'required|array|min:1',
            'businesses.*.name' => 'nullable|string',
            'businesses.*.phone' => 'nullable|string',
            'businesses.*.email' => 'nullable|string',
            'businesses.*.address' => 'nullable|string',
            'businesses.*.city' => 'nullable|string',
            'businesses.*.state' => 'nullable|string',
            'businesses.*.country' => 'nullable|string',
            'businesses.*.postal_code' => 'nullable|string',
            'businesses.*.website' => 'nullable|string',
            'businesses.*.category' => 'nullable|string',
            'businesses.*.rating' => 'nullable|numeric',
            'businesses.*.review_count' => 'nullable|integer',
            'businesses.*.place_id' => 'nullable|string',
            'businesses.*.maps_url' => 'nullable|string',
            'businesses.*.latitude' => 'nullable|numeric',
            'businesses.*.longitude' => 'nullable|numeric',
        ]);

        $user = $request->user() ?? \App\Models\User::first();

        $summary = $this->importService->importBatch(
            $user,
            $validated['businesses']
        );

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * GET /api/v1/businesses
     * List user businesses with search & filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;
        $query = Business::where('user_id', $userId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('place_id', 'like', "%{$search}%");
            });
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($minRating = $request->input('rating')) {
            $query->where('rating', '>=', (float) $minRating);
        }

        $perPage = (int) $request->input('per_page', 50);
        $businesses = $query->latest('id')->paginate(min($perPage, 250));

        return response()->json([
            'success' => true,
            'data' => $businesses,
        ]);
    }

    /**
     * GET /api/v1/businesses/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;
        $business = Business::where('user_id', $userId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $business,
        ]);
    }

    /**
     * DELETE /api/v1/businesses/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;
        $business = Business::where('user_id', $userId)->findOrFail($id);
        $business->delete();

        return response()->json([
            'success' => true,
            'message' => 'Business deleted successfully',
        ]);
    }

    /**
     * POST /api/v1/businesses/check-duplicates
     */
    public function checkDuplicates(Request $request): JsonResponse
    {
        $request->validate([
            'place_ids' => 'nullable|array',
            'place_ids.*' => 'string',
        ]);

        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;
        $placeIds = $request->input('place_ids', []);
        $existingPlaceIds = Business::where('user_id', $userId)
            ->whereIn('place_id', $placeIds)
            ->pluck('place_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'existing_place_ids' => $existingPlaceIds,
        ]);
    }
}
