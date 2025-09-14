<?php

namespace Mintreu\LaravelCommerinity\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SaleActionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case BY_PERCENT = 'by_percent';
    case TO_FIXED   = 'to_fixed';
    case TO_PERCENT = 'to_percent';
    case BY_FIXED   = 'by_fixed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BY_PERCENT => 'info',
            self::BY_FIXED   => 'success',
            self::TO_FIXED   => 'warning',
            self::TO_PERCENT => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'heroicon-o-percent-badge',
            self::BY_FIXED   => 'heroicon-o-currency-dollar',
            self::TO_FIXED   => 'heroicon-o-banknotes',
            self::TO_PERCENT => 'heroicon-o-receipt-percent',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BY_PERCENT => 'Percentage of Product Price',
            self::BY_FIXED   => 'Fixed Amount Off',
            self::TO_FIXED   => 'Set Product Price (Fixed)',
            self::TO_PERCENT => 'Set Product Price (Percentage)',
        };
    }
}
