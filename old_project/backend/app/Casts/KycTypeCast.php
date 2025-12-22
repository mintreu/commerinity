<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum KycTypeCast: string implements HasLabel, HasIcon, HasColor
{

    case INDIVIDUAL = 'individual';
    case BUSINESS = 'business';

    /**
     * @return string|array|null
     */
    public function getColor(): string|array|null
    {
        return match($this) {
            self::INDIVIDUAL => 'success',
            self::BUSINESS => 'primary',
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match($this) {
            self::INDIVIDUAL => 'heroicon-o-user',
            self::BUSINESS => 'heroicon-s-briefcase',
        };
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return match($this) {
            self::INDIVIDUAL => 'Individual',
            self::BUSINESS => 'Business',
        };
    }
}

