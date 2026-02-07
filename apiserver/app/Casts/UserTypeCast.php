<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case REGULAR = 'regular';     // Default - non-subscribed customer
    case MEMBER = 'member';       // Subscribed with active membership
    case PROMOTER = 'promoter';   // Actively refers others (Affiliate participant)
    case ADVISOR = 'advisor';     // Company-appointed, gets salary
    case MENTOR = 'mentor';       // Trains users, gets training fees

    public function getLabel(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::MEMBER => 'Member',
            self::PROMOTER => 'Promoter',
            self::ADVISOR => 'Advisor',
            self::MENTOR => 'Mentor',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::REGULAR => 'gray',
            self::MEMBER => 'success',
            self::PROMOTER => 'info',
            self::ADVISOR => 'primary',
            self::MENTOR => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REGULAR => 'heroicon-o-user',
            self::MEMBER => 'heroicon-m-check-badge',
            self::PROMOTER => 'heroicon-o-share',
            self::ADVISOR => 'heroicon-o-briefcase',
            self::MENTOR => 'heroicon-o-academic-cap',
        };
    }
}
