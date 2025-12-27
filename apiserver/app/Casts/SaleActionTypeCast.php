<?php

declare(strict_types=1);

namespace App\Casts;

use App\Services\MoneyService;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Sale/Discount Action Types
 *
 * BY_* = Discount amount (subtract from price)
 * TO_* = Set price to specific value
 */
enum SaleActionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case BY_PERCENT = 'by_percent';  // Take X% off original price
    case BY_FIXED = 'by_fixed';       // Take X rupees off original price
    case TO_PERCENT = 'to_percent';   // Set price to X% of original
    case TO_FIXED = 'to_fixed';       // Set price to fixed amount

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BY_PERCENT => 'info',
            self::BY_FIXED => 'success',
            self::TO_PERCENT => 'primary',
            self::TO_FIXED => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'heroicon-o-percent-badge',
            self::BY_FIXED => 'heroicon-o-currency-rupee',
            self::TO_PERCENT => 'heroicon-o-receipt-percent',
            self::TO_FIXED => 'heroicon-o-banknotes',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Percentage Off',
            self::BY_FIXED => 'Fixed Amount Off',
            self::TO_PERCENT => 'Set to Percentage',
            self::TO_FIXED => 'Set to Fixed Price',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Take % off. Ex: 10% of ₹450 → ₹405',
            self::BY_FIXED => 'Take fixed off. Ex: ₹10 of ₹450 → ₹440',
            self::TO_PERCENT => 'Set price to % of original. Ex: 10% of ₹450 → ₹45',
            self::TO_FIXED => 'Set price to fixed amount. Ex: ₹10 (was ₹450)',
        };
    }

    /**
     * Format the discount value with appropriate unit
     */
    public function formatValue(int $value): string
    {
        return match ($this) {
            self::BY_PERCENT,
            self::TO_PERCENT => $value.'%',
            self::BY_FIXED,
            self::TO_FIXED => MoneyService::format($value),
        };
    }

    /**
     * Calculate the final price after applying this discount
     *
     * @param  int  $originalPrice  Original price in paise
     * @param  int  $discountValue  Discount value (percentage or paise depending on type)
     * @return int Final price in paise
     */
    public function calculatePrice(int $originalPrice, int $discountValue): int
    {
        return match ($this) {
            self::BY_PERCENT => (int) round($originalPrice * (1 - $discountValue / 100)),
            self::BY_FIXED => max(0, $originalPrice - $discountValue),
            self::TO_PERCENT => (int) round($originalPrice * $discountValue / 100),
            self::TO_FIXED => $discountValue,
        };
    }

    /**
     * Calculate the discount amount
     *
     * @param  int  $originalPrice  Original price in paise
     * @param  int  $discountValue  Discount value
     * @return int Discount amount in paise
     */
    public function calculateDiscount(int $originalPrice, int $discountValue): int
    {
        $finalPrice = $this->calculatePrice($originalPrice, $discountValue);

        return max(0, $originalPrice - $finalPrice);
    }

    /**
     * Check if this is a percentage-based discount
     */
    public function isPercentage(): bool
    {
        return in_array($this, [self::BY_PERCENT, self::TO_PERCENT]);
    }

    /**
     * Check if this is a fixed-amount discount
     */
    public function isFixed(): bool
    {
        return in_array($this, [self::BY_FIXED, self::TO_FIXED]);
    }

    /**
     * Check if this discount subtracts from price (vs setting price)
     */
    public function isSubtractive(): bool
    {
        return in_array($this, [self::BY_PERCENT, self::BY_FIXED]);
    }
}
