<?php

namespace Mintreu\LaravelCommerinity\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VoucherActionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case BY_PERCENT = 'by_percent';
    case BY_FIXED = 'by_fixed';
    case CART_FIXED = 'cart_fixed';
    case CART_PERCENT = 'cart_percent';
    case BY_X_GET_Y = 'buy_x_get_y';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BY_PERCENT => 'info',
            self::BY_FIXED => 'success',
            self::CART_FIXED => 'warning',
            self::CART_PERCENT => 'primary',
            self::BY_X_GET_Y => 'purple',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'heroicon-o-percent-badge',
            self::BY_FIXED => 'heroicon-o-currency-dollar',
            self::CART_FIXED => 'heroicon-o-banknotes',
            self::CART_PERCENT => 'heroicon-o-receipt-percent',
            self::BY_X_GET_Y => 'heroicon-o-gift',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Percentage of Product Price',
            self::BY_FIXED => 'Fixed Amount',
            self::CART_FIXED => 'Fixed Amount to Whole Cart',
            self::CART_PERCENT => 'Cart Percentage',
            self::BY_X_GET_Y => 'Buy X Get Y',
        };
    }
}
