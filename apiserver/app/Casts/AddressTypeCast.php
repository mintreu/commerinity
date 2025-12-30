<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Address Type Cast Enum
 *
 * Defines types of addresses for user purposes.
 * Compatible with Filament v4 for form/table displays.
 */
enum AddressTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';

    /**
     * Get color for Filament UI
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HOME => 'success',
            self::WORK => Color::Emerald,
            self::OTHER => Color::Sky,
        };
    }

    /**
     * Get icon for Filament UI
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::HOME => 'heroicon-s-home',
            self::WORK => 'heroicon-o-building-office',
            self::OTHER => 'heroicon-s-map-pin',
        };
    }

    /**
     * Get label for Filament UI
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::HOME => 'Home',
            self::WORK => 'Work',
            self::OTHER => 'Other',
        };
    }
}
