<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RewardTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case COINS = 'coins';
    case VOUCHER = 'voucher';

    public function getLabel(): string
    {
        return match ($this) {
            self::COINS => 'Coins',
            self::VOUCHER => 'Voucher',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::COINS => 'success',
            self::VOUCHER => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::COINS => 'heroicon-o-currency-rupee',
            self::VOUCHER => 'heroicon-o-ticket',
        };
    }
}
