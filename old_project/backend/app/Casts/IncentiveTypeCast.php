<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum IncentiveTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case REFERRAL   = 'referral';   // earned from direct/indirect referral
    case BONUS      = 'bonus';      // joining bonus, seasonal bonus, etc.
    case MATCHING   = 'matching';   // matching bonus (when your team earns)
    case REWARD     = 'reward';     // reward points, loyalty etc.
    case ADJUSTMENT = 'adjustment'; // manual incentive adjustment

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REFERRAL   => 'success',
            self::BONUS      => 'primary',
            self::MATCHING   => 'info',
            self::REWARD     => 'warning',
            self::ADJUSTMENT => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REFERRAL   => 'heroicon-o-user-plus',
            self::BONUS      => 'heroicon-o-gift',
            self::MATCHING   => 'heroicon-o-link',
            self::REWARD     => 'heroicon-o-star',
            self::ADJUSTMENT => 'heroicon-o-cog-6-tooth',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REFERRAL   => 'Referral Incentive',
            self::BONUS      => 'Bonus',
            self::MATCHING   => 'Matching Incentive',
            self::REWARD     => 'Reward Points',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
}
