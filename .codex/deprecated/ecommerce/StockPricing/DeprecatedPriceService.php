<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\StockPricing;

use App\Models\Address;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Services\MoneyService;

/**
 * @deprecated Use App\Services\Ecommerce\PriceCalculationService or direct Product::price access instead.
 * This class preserves the old stock-based pricing reference for inventory or distributor logic.
 */
final class DeprecatedPriceService
{
    public function __construct(
        private readonly MoneyService $moneyService,
    ) {}

    /**
     * Calculate price based on landing cost and profit margin
     * Returns price in paise (integer)
     */
    public function calculateFromCost(int $landingCostPaise, float $profitMarginPercent): int
    {
        $marginMultiplier = 1 + ($profitMarginPercent / 100);
        $price = (int) round($landingCostPaise * $marginMultiplier);

        return max(0, $price); // Ensure non-negative
    }

    /**
     * Get effective price for a product stock entry
     * - Uses override price if set
     * - Otherwise calculates from landing cost + profit margin
     * - Returns price in paise
     */
    public function getStockPrice(ProductStock $stock): int
    {
        // Use override price if explicitly set
        if ($stock->price !== null && $stock->price > 0) {
            return $stock->price;
        }

        // Calculate from landing cost and profit margin
        return $this->calculateFromCost(
            $stock->landing_cost,
            (float) $stock->profit_margin
        );
    }

    /**
     * Get price for a product based on available stock (FIFO)
     */
    public function getProductPriceFromAvailableStock(iterable $availableStocks): int
    {
        // FIFO: Get first available stock
        foreach ($availableStocks as $stock) {
            if ($stock->inStock()) {
                return $this->getStockPrice($stock);
            }
        }

        return 0; // No stock available
    }

    /**
     * Get minimum price from multiple stock entries (for price range display)
     */
    public function getMinimumPrice(iterable $stocks): int
    {
        $minPrice = PHP_INT_MAX;

        foreach ($stocks as $stock) {
            if ($stock->inStock()) {
                $price = $this->getStockPrice($stock);
                $minPrice = min($minPrice, $price);
            }
        }

        return $minPrice === PHP_INT_MAX ? 0 : $minPrice;
    }

    /**
     * Get maximize price from multiple stock entries (for price range display)
     */
    public function getMaximumPrice(iterable $stocks): int
    {
        $maxPrice = 0;

        foreach ($stocks as $stock) {
            if ($stock->inStock()) {
                $price = $this->getStockPrice($stock);
                $maxPrice = max($maxPrice, $price);
            }
        }

        return $maxPrice;
    }

    /**
     * Get price range for display (like "₹199 - ₹299")
     */
    public function getPriceRange(iterable $stocks): string
    {
        $min = $this->getMinimumPrice($stocks);
        $max = $this->getMaximumPrice($stocks);

        if ($min === 0 && $max === 0) {
            return 'Out of stock';
        }

        if ($min === $max) {
            return $this->moneyService->format($min);
        }

        return $this->moneyService->format($min).' - '.$this->moneyService->format($max);
    }

    /**
     * Get cheapest available stock for a product (for cart/checkout)
     * Returns null if no stock available
     */
    public function getCheapestAvailableStock(iterable $availableStocks): ?ProductStock
    {
        $cheapestStock = null;
        $cheapestPrice = PHP_INT_MAX;

        foreach ($availableStocks as $stock) {
            if ($stock->inStock()) {
                $price = $this->getStockPrice($stock);
                if ($price < $cheapestPrice) {
                    $cheapestPrice = $price;
                    $cheapestStock = $stock;
                }
            }
        }

        return $cheapestStock;
    }

    /**
     * Get the best stock entry for a specific context (User Location)
     * Prioritizes:
     * 1. Closest Warehouse (postal code match, then state code)
     * 2. FIFO (created_at)
     */
    public function getBestStockForContext(iterable $stocks, ?array $context = null): ?ProductStock
    {
        $ordered = $this->getOrderedStocksForContext($stocks, $context);

        return $ordered[0] ?? null;
    }

    /**
     * Get correct price for this product context
     */
    public function getContextPrice(Product $product, ?array $context = null): int
    {
        $stock = $this->getBestStockForContext($product->availableStocks, $context);

        if (! $stock) {
            return 0;
        }

        return $this->getStockPrice($stock);
    }

    /**
     * Resolve stock context from address and overrides.
     */
    public function resolveStockContext(?Address $address = null, ?array $context = null): array
    {
        $resolved = [];

        if ($address) {
            if (! empty($address->postal_code)) {
                $resolved['postal_code'] = trim((string) $address->postal_code);
            }

            if (! empty($address->state_code)) {
                $resolved['state_code'] = strtoupper(trim((string) $address->state_code));
            }
        }

        $overrides = [
            'postal_code' => $context['postal_code'] ?? $context['pincode'] ?? null,
            'state_code' => $context['state_code'] ?? $context['state'] ?? null,
        ];

        foreach ($overrides as $key => $value) {
            if ($value !== null && $value !== '') {
                $resolved[$key] = $key === 'state_code'
                    ? strtoupper(trim((string) $value))
                    : trim((string) $value);
            }
        }

        return $resolved;
    }

    /**
     * Order stocks based on context and FIFO.
     */
    public function getOrderedStocksForContext(iterable $stocks, ?array $context = null): array
    {
        $available = [];
        foreach ($stocks as $stock) {
            if ($stock->inStock()) {
                $available[] = $stock;
            }
        }

        if (empty($available)) {
            return [];
        }

        $resolvedContext = $this->resolveStockContext(null, $context);
        $postalCode = $resolvedContext['postal_code'] ?? null;
        $stateCode = $resolvedContext['state_code'] ?? null;

        usort($available, function (ProductStock $a, ProductStock $b) use ($postalCode, $stateCode): int {
            $aRank = $this->getStockMatchRank($a, $postalCode, $stateCode);
            $bRank = $this->getStockMatchRank($b, $postalCode, $stateCode);

            if ($aRank !== $bRank) {
                return $aRank <=> $bRank;
            }

            if ($aRank < 2) {
                $expiryComparison = $this->compareByExpiry($a, $b);
                if ($expiryComparison !== 0) {
                    return $expiryComparison;
                }
            }

            $aCreated = $a->created_at?->getTimestamp() ?? 0;
            $bCreated = $b->created_at?->getTimestamp() ?? 0;

            if ($aCreated !== $bCreated) {
                return $aCreated <=> $bCreated;
            }

            return $a->id <=> $b->id;
        });

        return $available;
    }

    private function getStockMatchRank(ProductStock $stock, ?string $postalCode, ?string $stateCode): int
    {
        if (! $postalCode && ! $stateCode) {
            return 0;
        }

        $stockPostal = $stock->address?->postal_code ? trim((string) $stock->address->postal_code) : null;
        $stockState = $stock->address?->state_code ? strtoupper(trim((string) $stock->address->state_code)) : null;

        if ($postalCode && $stockPostal && $stockPostal === $postalCode) {
            return 0;
        }

        if ($stateCode && $stockState && $stockState === $stateCode) {
            return 1;
        }

        return 2;
    }

    private function compareByExpiry(ProductStock $a, ProductStock $b): int
    {
        $aExpiry = $a->expiry_date?->getTimestamp();
        $bExpiry = $b->expiry_date?->getTimestamp();

        if ($aExpiry !== null && $bExpiry !== null) {
            if ($aExpiry !== $bExpiry) {
                return $aExpiry <=> $bExpiry;
            }
        } elseif ($aExpiry !== null || $bExpiry !== null) {
            return $aExpiry !== null ? -1 : 1;
        }

        if ($a->batch_number !== null && $b->batch_number !== null && $a->batch_number !== $b->batch_number) {
            return strcmp($a->batch_number, $b->batch_number);
        }

        return 0;
    }
}
