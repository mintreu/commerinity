<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WalletStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case FROZEN = 'frozen';
    case CLOSED = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::FROZEN => 'Frozen',
            self::CLOSED => 'Closed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SUSPENDED => 'warning',
            self::FROZEN => 'info',
            self::CLOSED => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::SUSPENDED => 'heroicon-o-pause-circle',
            self::FROZEN => 'heroicon-o-lock-closed',
            self::CLOSED => 'heroicon-o-x-circle',
        };
    }

    public function canTransact(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::ACTIVE, self::SUSPENDED]);
    }
}
