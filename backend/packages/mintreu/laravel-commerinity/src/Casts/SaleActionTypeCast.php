<?php

namespace Mintreu\LaravelCommerinity\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SaleActionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case BY_PERCENT = 'by_percent';
    case BY_FIXED   = 'by_fixed';
    case TO_PERCENT = 'to_percent';
    case TO_FIXED   = 'to_fixed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BY_PERCENT => 'info',
            self::BY_FIXED   => 'success',
            self::TO_PERCENT => 'primary',
            self::TO_FIXED   => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'heroicon-o-percent-badge',
            self::BY_FIXED   => 'heroicon-o-currency-dollar',
            self::TO_PERCENT => 'heroicon-o-receipt-percent',
            self::TO_FIXED   => 'heroicon-o-banknotes',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Percentage of Product Price',
            self::BY_FIXED   => 'Fixed Amount Off',
            self::TO_PERCENT => 'Set Product Price (Percentage)',
            self::TO_FIXED   => 'Set Product Price (Fixed)',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Take % off. Ex: 10% of ₹450 → ₹405',
            self::BY_FIXED   => 'Take fixed off. Ex: ₹10 of ₹450 → ₹440',
            self::TO_PERCENT => 'Set price to % of original. Ex: 10% of ₹450 → ₹45',
            self::TO_FIXED   => 'Set price to fixed amount. Ex: ₹10 (was ₹450)',
        };
    }

}
