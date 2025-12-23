<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BeneficiaryTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SAVINGS = 'savings';
    case CURRENT = 'current';
    case UPI = 'upi';

    public function getLabel(): string
    {
        return match ($this) {
            self::SAVINGS => 'Savings Account',
            self::CURRENT => 'Current Account',
            self::UPI => 'UPI',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SAVINGS => 'success',
            self::CURRENT => 'info',
            self::UPI => 'primary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SAVINGS => 'heroicon-o-building-library',
            self::CURRENT => 'heroicon-o-building-office',
            self::UPI => 'heroicon-o-qr-code',
        };
    }

    public function isBank(): bool
    {
        return in_array($this, [self::SAVINGS, self::CURRENT]);
    }

    public function isUpi(): bool
    {
        return $this === self::UPI;
    }
}
