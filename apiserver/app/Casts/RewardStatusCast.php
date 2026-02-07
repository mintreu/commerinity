<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RewardStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case ISSUED = 'issued';
    case CLAIMED = 'claimed';
    case USED = 'used';
    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::ISSUED => 'Issued',
            self::CLAIMED => 'Claimed',
            self::USED => 'Used',
            self::EXPIRED => 'Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ISSUED => 'info',
            self::CLAIMED => 'warning',
            self::USED => 'success',
            self::EXPIRED => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ISSUED => 'heroicon-o-ticket',
            self::CLAIMED => 'heroicon-o-check',
            self::USED => 'heroicon-o-check-circle',
            self::EXPIRED => 'heroicon-o-x-circle',
        };
    }
}
