<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\ProductStock;
use App\Services\MoneyService;

/**
 * Centralized pricing calculation service for ecommerce products
 *
 * Follows Amazon/Flipkart pattern: Price determined by inventory (stock) entry
 * - Each stock entry has landing_cost + profit_margin → calculated price
 * - Can optionally have override price
 * - FIFO (first-in, first-out) for available stock
 * - Supports wholesale pricing tiers
 * - Integrates with sale system
 */
final class PriceCalculationService
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
     * Returns 0 if no stock available
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
     * Calculate sale price based on sale action
     * Supports percentage, fixed amount, and fixed price actions
     */
    public function calculateSalePrice(int $originalPricePaise, string $actionType, float $actionValue): int
    {
        return match ($actionType) {
            'percentage_off' => $originalPricePaise - (int) round($originalPricePaise * ($actionValue / 100)),
            'fixed_amount_off' => max(0, $originalPricePaise - (int) round($actionValue * 100)), // Convert to paise
            'fixed_price' => (int) round($actionValue * 100), // Convert to paise
            default => $originalPricePaise,
        };
    }

    /**
     * Check if quantity qualifies for wholesale pricing
     */
    public function isWholesaleQuantity(int $quantity, ?int $wholesaleUnitQuantity): bool
    {
        return $wholesaleUnitQuantity !== null && $quantity >= $wholesaleUnitQuantity;
    }

    /**
     * Format price for display
     */
    public function formatPrice(int $pricePaise): string
    {
        return $this->moneyService->format($pricePaise);
    }

    /**
     * Calculate discount percentage
     */
    public function calculateDiscountPercent(int $originalPrice, int $salePrice): float
    {
        if ($originalPrice <= 0 || $salePrice >= $originalPrice) {
            return 0.0;
        }

        return round((($originalPrice - $salePrice) / $originalPrice) * 100, 2);
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
     * Get maximum price from multiple stock entries (for price range display)
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
            return $this->formatPrice($min);
        }

        return $this->formatPrice($min) . ' - ' . $this->formatPrice($max);
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
     * Get stock with closest warehouse to shipping address
     * Requires address comparison logic (simplified here)
     */
    public function getClosestWarehouseStock(iterable $availableStocks, ?array $shippingAddress = null): ?ProductStock
    {
        // If no address provided, return cheapest
        if ($shippingAddress === null) {
            return $this->getCheapestAvailableStock($availableStocks);
        }

        // Simplified: For now, return first available stock
        // In production, implement address-based distance calculation
        foreach ($availableStocks as $stock) {
            if ($stock->inStock()) {
                return $stock;
            }
        }

        return null;
    }

    /**
     * Calculate total price for quantity from specific stock
     */
    public function calculateTotalFromStock(ProductStock $stock, int $quantity): int
    {
        return $this->getStockPrice($stock) * $quantity;
    }

    /**
     * Validate if stock has enough quantity for purchase
     */
    public function validateStockQuantity(ProductStock $stock, int $quantity): bool
    {
        return $stock->inStock() && $stock->available_stock >= $quantity;
    }
}