<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected string $apiKey;
    protected string $placeId;
    protected const CACHE_KEY = 'google-reviews';
    protected const CACHE_DURATION = 3600 * 24; // Cache reviews for 6 hours

    public function __construct()
    {
        $this->apiKey = config('services.google.places_api_key');
        $this->placeId = config('services.google.place_id');
    }

    /**
     * Fetch the latest Google reviews, using cache to avoid excessive API calls.
     */
    public function getReviews(): array
    {
        if (empty($this->apiKey) || empty($this->placeId)) {
            Log::warning('Google Places API Key or Place ID is not configured.');
            return [];
        }

        // 'remember' will get the item from cache or execute the closure and store the result.
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $this->placeId,
                    'fields' => 'reviews',
                    'key' => $this->apiKey,
                    'language' => 'fr', // Get reviews in French
                ]);

                if ($response->successful() && isset($response->json()['result']['reviews'])) {
                    return $response->json()['result']['reviews'];
                }

                Log::error('Failed to fetch Google reviews.', $response->json());
                return [];

            } catch (\Exception $e) {
                Log::error('Exception while fetching Google reviews: ' . $e->getMessage());
                return [];
            }
        });
    }
}
