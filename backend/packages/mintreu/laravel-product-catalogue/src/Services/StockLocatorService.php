<?php

namespace Mintreu\LaravelProductCatalogue\Services;

use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;

class StockLocatorService
{
    private const MATCH_SCORE_COORDS = 0;
    private const MATCH_SCORE_POSTAL = 1;
    private const MATCH_SCORE_CITY_STATE = 2;
    private const MATCH_SCORE_BLOCK = 3;
    private const MATCH_SCORE_STATE = 4;
    private const MATCH_SCORE_COUNTRY = 5;
    private const MATCH_SCORE_NONE = 6;


    /**
     * Finds the most suitable ProductTier based on geographic proximity and FIFO.
     *
     * @param Product $product
     * @param Address $customerAddress
     * @return ProductTier|null
     */
    public function find(Product $product, Address $customerAddress): ?ProductTier
    {
        $availableTiers = $product->tiers()
            ->where('init_quantity', '>', 'sold_quantity')
            ->whereHas('address')
            ->with('address')
            ->get();

        if ($availableTiers->isEmpty()) {
            return null;
        }

        $scoredTiers = $availableTiers->map(function (ProductTier $tier) use ($customerAddress) {
            $this->assignMatchScore($tier, $customerAddress);
            return $tier;
        });

        $sortedTiers = $scoredTiers->sortBy([
            ['match_score', 'asc'],
            ['distance', 'asc'],
            ['created_at', 'asc'],
        ]);

        return $sortedTiers->first();
    }

    /**
     * Assigns a match score and distance to a tier based on address comparison.
     *
     * @param ProductTier $tier
     * @param Address $customerAddress
     */
    private function assignMatchScore(ProductTier $tier, Address $customerAddress): void
    {
        $tierAddress = $tier->address;
        $tier->distance = PHP_FLOAT_MAX; // Default high distance

        // Level 0: Coordinate-based distance
        if ($customerAddress->latitude && $customerAddress->longitude && $tierAddress->latitude && $tierAddress->longitude) {
            $tier->match_score = self::MATCH_SCORE_COORDS;
            $tier->distance = $this->calculateDistance(
                $customerAddress->latitude, $customerAddress->longitude,
                $tierAddress->latitude, $tierAddress->longitude
            );
            return;
        }

        // Fallback Levels
        if ($customerAddress->postal_code && $customerAddress->postal_code === $tierAddress->postal_code) {
            $tier->match_score = self::MATCH_SCORE_POSTAL;
            return;
        }

        if ($customerAddress->city && $customerAddress->state_code && $customerAddress->city === $tierAddress->city && $customerAddress->state_code === $tierAddress->state_code) {
            $tier->match_score = self::MATCH_SCORE_CITY_STATE;
            return;
        }

        if ($customerAddress->block_id && $customerAddress->block_id === $tierAddress->block_id) {
            $tier->match_score = self::MATCH_SCORE_BLOCK;
            return;
        }

        if ($customerAddress->state_code && $customerAddress->state_code === $tierAddress->state_code) {
            $tier->match_score = self::MATCH_SCORE_STATE;
            return;
        }
        
        if ($customerAddress->country_code && $customerAddress->country_code === $tierAddress->country_code) {
            $tier->match_score = self::MATCH_SCORE_COUNTRY;
            return;
        }

        $tier->match_score = self::MATCH_SCORE_NONE;
    }


    /**
     * Calculates distance using the Haversine formula.
     * @return float Distance in kilometers.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}