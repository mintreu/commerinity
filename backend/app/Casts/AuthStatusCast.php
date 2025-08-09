<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AuthStatusCast: string implements HasColor, HasIcon, HasLabel
{
    // Pre-onboarding / Onboarding
    case DRAFT        = 'draft';
    case INCOMPLETE   = 'incomplete';
    case IN_REVIEW    = 'in_review';

    // Core states
    case PENDING      = 'pending';
    case ACTIVE       = 'active';
    case SUSPENDED    = 'suspended';
    case INACTIVE     = 'inactive';
    case BANNED       = 'banned';

    // Subscription / Program status
    case SUBSCRIBED   = 'subscribed';
    case UNSUBSCRIBED = 'unsubscribed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT        => 'Draft',
            self::INCOMPLETE   => 'Incomplete',
            self::IN_REVIEW    => 'In Review',
            self::PENDING      => 'Pending',
            self::ACTIVE       => 'Active',
            self::SUSPENDED    => 'Suspended',
            self::INACTIVE     => 'Inactive',
            self::BANNED       => 'Banned',
            self::SUBSCRIBED   => 'Subscribed',
            self::UNSUBSCRIBED => 'Unsubscribed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT        => 'gray',
            self::INCOMPLETE   => 'gray',
            self::IN_REVIEW    => 'info',
            self::PENDING      => 'warning',
            self::ACTIVE       => 'success',
            self::SUSPENDED    => 'warning',
            self::INACTIVE     => 'secondary',
            self::BANNED       => 'danger',
            self::SUBSCRIBED   => 'primary',
            self::UNSUBSCRIBED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT        => 'heroicon-o-pencil',
            self::INCOMPLETE   => 'heroicon-o-exclamation-circle',
            self::IN_REVIEW    => 'heroicon-o-eye',
            self::PENDING      => 'heroicon-o-clock',
            self::ACTIVE       => 'heroicon-o-check-badge',
            self::SUSPENDED    => 'heroicon-o-pause',
            self::INACTIVE     => 'heroicon-o-user-minus',
            self::BANNED       => 'heroicon-o-no-symbol',
            self::SUBSCRIBED   => 'heroicon-o-sparkles',
            self::UNSUBSCRIBED => 'heroicon-o-bell-slash',
        };
    }
}
