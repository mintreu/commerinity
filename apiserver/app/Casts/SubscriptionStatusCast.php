<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case UPGRADED = 'upgraded';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
            self::UPGRADED => 'Upgraded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::EXPIRED => 'danger',
            self::CANCELLED => 'gray',
            self::UPGRADED => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::ACTIVE => 'heroicon-o-check-circle',
            self::EXPIRED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-minus-circle',
            self::UPGRADED => 'heroicon-o-arrow-trending-up',
        };
    }

    public function isValid(): bool
    {
        return $this === self::ACTIVE;
    }
}
