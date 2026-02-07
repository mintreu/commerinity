<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum KycTypeCast: string implements HasLabel, HasIcon, HasColor
{
    case PERSONAL = 'personal';
    case BUSINESS = 'business';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PERSONAL => 'success',
            self::BUSINESS => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PERSONAL => 'heroicon-o-user',
            self::BUSINESS => 'heroicon-s-briefcase',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PERSONAL => 'Personal',
            self::BUSINESS => 'Business',
        };
    }
}
