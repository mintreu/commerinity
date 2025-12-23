<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskAudienceCast: string implements HasColor, HasIcon, HasLabel
{
    case All = 'all';
    case User = 'user';
    case Member = 'member';
    case Promoter = 'promoter';
    case Advisor = 'advisor';
    case Mentor = 'mentor';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => __('helpdesk.audience.all'),
            self::User => __('helpdesk.audience.user'),
            self::Member => __('helpdesk.audience.member'),
            self::Promoter => __('helpdesk.audience.promoter'),
            self::Advisor => __('helpdesk.audience.advisor'),
            self::Mentor => __('helpdesk.audience.mentor'),
            self::Admin => __('helpdesk.audience.admin'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::All => 'gray',
            self::User => 'info',
            self::Member => 'success',
            self::Promoter => 'warning',
            self::Advisor => 'primary',
            self::Mentor => 'danger',
            self::Admin => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::All => 'heroicon-o-globe-alt',
            self::User => 'heroicon-o-user',
            self::Member => 'heroicon-o-user-circle',
            self::Promoter => 'heroicon-o-megaphone',
            self::Advisor => 'heroicon-o-academic-cap',
            self::Mentor => 'heroicon-o-star',
            self::Admin => 'heroicon-o-shield-check',
        };
    }

    public static function getDefault(): self
    {
        return self::All;
    }
}
