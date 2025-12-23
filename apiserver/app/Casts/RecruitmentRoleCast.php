<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RecruitmentRoleCast: string implements HasColor, HasIcon, HasLabel
{
    case Advisor = 'advisor';
    case Trainer = 'trainer';
    case Executive = 'executive';
    case Manager = 'manager';
    case Support = 'support';
    case Intern = 'intern';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Advisor => __('recruitment.roles.advisor'),
            self::Trainer => __('recruitment.roles.trainer'),
            self::Executive => __('recruitment.roles.executive'),
            self::Manager => __('recruitment.roles.manager'),
            self::Support => __('recruitment.roles.support'),
            self::Intern => __('recruitment.roles.intern'),
            self::Other => __('recruitment.roles.other'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Advisor => 'primary',
            self::Trainer => 'warning',
            self::Executive => 'info',
            self::Manager => 'success',
            self::Support => 'gray',
            self::Intern => 'purple',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Advisor => 'heroicon-o-light-bulb',
            self::Trainer => 'heroicon-o-academic-cap',
            self::Executive => 'heroicon-o-briefcase',
            self::Manager => 'heroicon-o-user-group',
            self::Support => 'heroicon-o-chat-bubble-left-right',
            self::Intern => 'heroicon-o-rocket-launch',
            self::Other => 'heroicon-o-squares-2x2',
        };
    }

    /**
     * Check if this role is currently open for applications.
     */
    public function isCurrentlyHiring(): bool
    {
        return in_array($this, [self::Advisor, self::Trainer, self::Intern]);
    }

    /**
     * Check if this role can be converted to staff after approval.
     */
    public function canBecomeStaff(): bool
    {
        return in_array($this, [self::Intern, self::Advisor, self::Trainer]);
    }
}
