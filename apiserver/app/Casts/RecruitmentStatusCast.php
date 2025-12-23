<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RecruitmentStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('recruitment.status.draft'),
            self::Published => __('recruitment.status.published'),
            self::Closed => __('recruitment.status.closed'),
            self::Archived => __('recruitment.status.archived'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Closed => 'warning',
            self::Archived => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Published => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-lock-closed',
            self::Archived => 'heroicon-o-archive-box',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Published;
    }
}
