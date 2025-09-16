<?php

namespace Mintreu\LaravelTransaction\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BeneficiaryAccountTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SAVINGS = 'savings';
    case CURRENT = 'current';
    case UPI = 'upi';

    /**
     * Human friendly label for UI
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::SAVINGS => 'Savings Bank Account',
            self::CURRENT => 'Current Bank Account',
            self::UPI     => 'UPI Handle',
        };
    }

    /**
     * Heroicon (Blade UI Kit)
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::SAVINGS => 'heroicon-o-banknotes',
            self::CURRENT => 'heroicon-o-building-library',
            self::UPI     => 'heroicon-o-qr-code',
        };
    }

    /**
     * Filament color mapping
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SAVINGS => 'success',
            self::CURRENT => 'info',
            self::UPI     => 'warning',
        };
    }
}
