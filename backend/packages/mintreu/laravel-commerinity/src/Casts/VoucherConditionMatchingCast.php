<?php

namespace Mintreu\LaravelCommerinity\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VoucherConditionMatchingCast: string implements HasColor, HasIcon, HasLabel
{
    case MATCH_ALL = 'match_all';
    case MATCH_ANY = 'match_any';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MATCH_ALL => 'success',
            self::MATCH_ANY => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MATCH_ALL => 'heroicon-o-check-circle',
            self::MATCH_ANY => 'heroicon-o-adjustments-horizontal',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MATCH_ALL => 'Match All Conditions',
            self::MATCH_ANY => 'Match Any Condition',
        };
    }
}
