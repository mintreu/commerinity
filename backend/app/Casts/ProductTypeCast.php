<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SIMPLE = 'simple';
    case CONFIGURABLE = 'configurable';
    case WHOLESALE = 'wholesale'; // Formerly BULK

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::CONFIGURABLE => 'Configurable',
            self::WHOLESALE => 'Wholesale',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SIMPLE => 'info',
            self::CONFIGURABLE => 'warning',
            self::WHOLESALE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SIMPLE => 'heroicon-m-cube',
            self::CONFIGURABLE => 'heroicon-m-adjustments-horizontal',
            self::WHOLESALE => 'heroicon-m-truck',
        };
    }

    public function getMedia(): string|array|null
    {
        return match ($this) {
            self::SIMPLE => asset('media/type_simple.jpeg'),
            self::CONFIGURABLE => asset('media/type_configurable.jpeg'),
            self::WHOLESALE => asset('media/type_wholesale.jpeg'),
        };
    }
}
