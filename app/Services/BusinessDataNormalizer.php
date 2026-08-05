<?php

namespace App\Services;

class BusinessDataNormalizer
{
    /**
     * Normalize a full raw business payload.
     */
    public function normalize(array $data): array
    {
        $name = $this->normalizeName($data['name'] ?? null);
        $phone = $this->normalizePhone($data['phone'] ?? null);
        $email = $this->normalizeEmail($data['email'] ?? null);
        $address = $this->normalizeWhitespace($data['address'] ?? null);
        $website = $this->normalizeUrl($data['website'] ?? null);
        $category = $this->normalizeWhitespace($data['category'] ?? null);
        $mapsUrl = $this->normalizeUrl($data['maps_url'] ?? null);
        $placeId = $this->normalizePlaceId($data['place_id'] ?? null);

        // Standardize rating and review count
        $rating = (isset($data['rating']) && is_numeric($data['rating'])) ? round((float) $data['rating'], 2) : null;
        $reviewCount = (isset($data['review_count']) && is_numeric($data['review_count'])) ? (int) $data['review_count'] : null;

        // Coordinates
        $latitude = (isset($data['latitude']) && is_numeric($data['latitude'])) ? (float) $data['latitude'] : null;
        $longitude = (isset($data['longitude']) && is_numeric($data['longitude'])) ? (float) $data['longitude'] : null;

        // Extract city, state, postal code, country from explicitly provided keys or parse from full address string
        $city = $this->normalizeWhitespace($data['city'] ?? null);
        $state = $this->normalizeWhitespace($data['state'] ?? null);
        $country = $this->normalizeWhitespace($data['country'] ?? null);
        $postalCode = $this->normalizeWhitespace($data['postal_code'] ?? null);

        if (!$city || !$state || !$postalCode) {
            $parsed = $this->parseAddressComponents($address);
            $city = $city ?: $parsed['city'];
            $state = $state ?: $parsed['state'];
            $postalCode = $postalCode ?: $parsed['postal_code'];
            $country = $country ?: $parsed['country'];
        }

        // Ensure category does not duplicate business name
        if ($category && $name && strtolower($category) === strtolower($name)) {
            $category = null;
        }

        return [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'postal_code' => $postalCode,
            'website' => $website,
            'category' => $category,
            'rating' => $rating,
            'review_count' => $reviewCount,
            'place_id' => $placeId,
            'maps_url' => $mapsUrl,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => $data['source'] ?? 'google_maps',
        ];
    }

    /**
     * Smart Address Parser for Indian & International Google Maps addresses
     */
    public function parseAddressComponents(?string $address): array
    {
        $components = [
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country' => null,
        ];

        if (!$address) {
            return $components;
        }

        $cleanAddress = preg_replace('/^Address:\s*/i', '', $address);

        // 1. Postal Code (5-6 digit zip code e.g. 364001)
        if (preg_match('/\b(\d{5,6})\b/', $cleanAddress, $zipMatch)) {
            $components['postal_code'] = $zipMatch[1];
        }

        // Known Indian States & Countries
        $states = [
            'gujarat', 'maharashtra', 'rajasthan', 'delhi', 'karnataka',
            'tamil nadu', 'kerala', 'punjab', 'haryana', 'uttar pradesh',
            'madhya pradesh', 'west bengal', 'telangana', 'andhra pradesh'
        ];

        $countries = [
            'india', 'usa', 'united states', 'uk', 'united kingdom',
            'canada', 'australia', 'uae'
        ];

        $parts = array_map('trim', explode(',', $cleanAddress));
        $count = count($parts);

        // Extract Country
        if ($count > 0 && in_array(strtolower($parts[$count - 1]), $countries)) {
            $components['country'] = ucfirst($parts[$count - 1]);
            array_pop($parts);
            $count = count($parts);
        }

        // Extract State and City
        if ($count > 0) {
            $lastPart = $parts[$count - 1];
            $cleanedLastPart = trim(preg_replace('/\b\d{5,6}\b/', '', $lastPart));

            if (in_array(strtolower($cleanedLastPart), $states)) {
                $components['state'] = ucfirst($cleanedLastPart);
                array_pop($parts);
                $count = count($parts);

                if ($count > 0) {
                    $components['city'] = $parts[$count - 1];
                }
            } else {
                if ($count >= 2) {
                    $components['state'] = $cleanedLastPart ?: null;
                    $components['city'] = $parts[$count - 2];
                } else {
                    $components['city'] = $cleanedLastPart;
                }
            }
        }

        // Clean up city string
        if ($components['city']) {
            $components['city'] = preg_replace('/^village:-\s*/i', '', $components['city']);
            $components['city'] = trim($components['city']);

            if (is_numeric($components['city']) || preg_match('/^(shop|plot|flat|no|301|401)\b/i', $components['city'])) {
                $components['city'] = null;
            }
        }

        return $components;
    }

    public function normalizeName(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        $cleaned = preg_replace('/\s+/', ' ', trim($name));
        return $cleaned !== '' ? $cleaned : null;
    }

    public function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $trimmed = trim($phone);
        $hasPlus = str_starts_with($trimmed, '+');
        $digitsOnly = preg_replace('/[^\d]/', '', $trimmed);

        if (empty($digitsOnly)) {
            return null;
        }

        return $hasPlus ? '+' . $digitsOnly : $digitsOnly;
    }

    public function normalizeEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $cleaned = strtolower(trim($email));
        return filter_var($cleaned, FILTER_VALIDATE_EMAIL) ? $cleaned : null;
    }

    public function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $trimmed = trim($url);
        if (empty($trimmed)) {
            return null;
        }

        if (!preg_match('~^(?:f|ht)tps?://~i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }

        return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : null;
    }

    public function normalizeWhitespace(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        $cleaned = preg_replace('/\s+/', ' ', trim($text));
        return $cleaned !== '' ? $cleaned : null;
    }

    public function normalizePlaceId(?string $placeId): ?string
    {
        if (!$placeId) {
            return null;
        }

        $cleaned = trim($placeId);
        return $cleaned !== '' ? $cleaned : null;
    }
}
