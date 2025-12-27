<?php

declare(strict_types=1);

namespace App\Casts;

use App\Services\MoneyService;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Voucher/Coupon Action Types
 *
 * Defines how a voucher discount is applied
 */
enum VoucherActionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case BY_PERCENT = 'by_percent';       // X% off each product
    case BY_FIXED = 'by_fixed';           // X rupees off each product
    case CART_FIXED = 'cart_fixed';       // X rupees off cart total
    case CART_PERCENT = 'cart_percent';   // X% off cart total
    case BUY_X_GET_Y = 'buy_x_get_y';     // Buy X get Y free

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BY_PERCENT => 'info',
            self::BY_FIXED => 'success',
            self::CART_FIXED => 'warning',
            self::CART_PERCENT => 'primary',
            self::BUY_X_GET_Y => 'purple',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'heroicon-o-percent-badge',
            self::BY_FIXED => 'heroicon-o-currency-rupee',
            self::CART_FIXED => 'heroicon-o-banknotes',
            self::CART_PERCENT => 'heroicon-o-receipt-percent',
            self::BUY_X_GET_Y => 'heroicon-o-gift',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Percentage Off (Per Item)',
            self::BY_FIXED => 'Fixed Amount Off (Per Item)',
            self::CART_FIXED => 'Fixed Amount Off (Cart)',
            self::CART_PERCENT => 'Percentage Off (Cart)',
            self::BUY_X_GET_Y => 'Buy X Get Y Free',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Take X% off each eligible product',
            self::BY_FIXED => 'Take fixed amount off each eligible product',
            self::CART_FIXED => 'Take fixed amount off entire cart total',
            self::CART_PERCENT => 'Take X% off entire cart total',
            self::BUY_X_GET_Y => 'Buy X items, get Y items free',
        };
    }

    /**
     * Format the discount value with appropriate unit
     */
    public function formatValue(int $value): string
    {
        return match ($this) {
            self::BY_PERCENT,
            self::CART_PERCENT => $value.'%',
            self::BY_FIXED,
            self::CART_FIXED => MoneyService::format($value),
            self::BUY_X_GET_Y => 'Buy '.$value.' Get Free',
        };
    }

    /**
     * Calculate discount for a single item
     *
     * @param  int  $itemPrice  Price in paise
     * @param  int  $discountValue  Discount value (percentage or paise)
     * @return int Discount amount in paise
     */
    public function calculateItemDiscount(int $itemPrice, int $discountValue): int
    {
        return match ($this) {
            self::BY_PERCENT => (int) round($itemPrice * $discountValue / 100),
            self::BY_FIXED => min($itemPrice, $discountValue),
            // Cart-level discounts don't apply to individual items
            self::CART_FIXED,
            self::CART_PERCENT,
            self::BUY_X_GET_Y => 0,
        };
    }

    /**
     * Calculate discount for entire cart
     *
     * @param  int  $cartTotal  Cart total in paise
     * @param  int  $discountValue  Discount value (percentage or paise)
     * @return int Discount amount in paise
     */
    public function calculateCartDiscount(int $cartTotal, int $discountValue): int
    {
        return match ($this) {
            self::CART_PERCENT => (int) round($cartTotal * $discountValue / 100),
            self::CART_FIXED => min($cartTotal, $discountValue),
            // Item-level discounts should be calculated per item
            self::BY_PERCENT,
            self::BY_FIXED,
            self::BUY_X_GET_Y => 0,
        };
    }

    /**
     * Check if this is a cart-level discount
     */
    public function isCartLevel(): bool
    {
        return in_array($this, [self::CART_FIXED, self::CART_PERCENT]);
    }

    /**
     * Check if this is an item-level discount
     */
    public function isItemLevel(): bool
    {
        return in_array($this, [self::BY_PERCENT, self::BY_FIXED]);
    }

    /**
     * Check if this is a promotional discount (BOGO, etc.)
     */
    public function isPromotional(): bool
    {
        return $this === self::BUY_X_GET_Y;
    }

    /**
     * Check if this is a percentage-based discount
     */
    public function isPercentage(): bool
    {
        return in_array($this, [self::BY_PERCENT, self::CART_PERCENT]);
    }
}
