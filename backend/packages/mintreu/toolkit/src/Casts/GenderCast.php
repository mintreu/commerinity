<?php

namespace Mintreu\Toolkit\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum GenderCast: string implements HasColor, HasIcon, HasLabel
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    /**
     * @return string|array|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MALE => 'primary',   // or 'blue'
            self::FEMALE => 'pink',    // or 'rose'
            self::OTHER => 'gray',
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::MALE => 'heroicon-o-user-circle',
            self::FEMALE => 'heroicon-o-user-group',
            self::OTHER => 'heroicon-o-adjustments-horizontal',
        };
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        };
    }
}
