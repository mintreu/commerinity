<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AuthTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case REGULAR = 'regular';   // Non-subscribed user
    case MEMBER = 'member';     // Subscribed user
    case PROMOTER = 'promoter'; // Refers other users
    case ADVISOR = 'advisor';   // Company-appointed, gets salary
    case MENTOR = 'mentor';     // Trains users, gets paid

    public function getColor(): string
    {
        return match ($this) {
            self::REGULAR  => 'gray',
            self::MEMBER   => 'success',
            self::PROMOTER => 'info',
            self::ADVISOR  => 'primary',
            self::MENTOR   => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REGULAR  => 'heroicon-o-user',
            self::MEMBER   => 'heroicon-o-badge-check',
            self::PROMOTER => 'heroicon-o-share',
            self::ADVISOR  => 'heroicon-o-briefcase',
            self::MENTOR   => 'heroicon-o-academic-cap',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REGULAR  => 'Regular',
            self::MEMBER   => 'Member',
            self::PROMOTER => 'Promoter',
            self::ADVISOR  => 'Advisor',
            self::MENTOR   => 'Mentor',
        };
    }
}
