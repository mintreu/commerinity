<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Models\Address;
use App\Models\Ecommerce\ProductStock;
use App\Services\MoneyService;

final class PriceCalculationService
{
    public function __construct(
        private readonly MoneyService $moneyService,
    ) {}

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

    public function getBestStockForContext(iterable $stocks, ?array $context = null): ?ProductStock
    {
        return $this->getOrderedStocksForContext($stocks, $context)[0] ?? null;
    }

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
