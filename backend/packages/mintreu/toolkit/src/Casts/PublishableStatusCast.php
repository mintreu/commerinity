<?php

namespace Mintreu\Toolkit\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PublishableStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT        = 'draft';
    case PENDING      = 'pending';
    case REVIEW       = 'review';
    case NEEDS_ACTION = 'needs_action';
    case PUBLISHED    = 'published';
    case REJECTED     = 'rejected';
    case ARCHIVED     = 'archived';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT        => 'Draft',
            self::PENDING      => 'Pending',
            self::REVIEW       => 'Review',
            self::NEEDS_ACTION => 'Needs Action',
            self::PUBLISHED    => 'Published',
            self::REJECTED     => 'Rejected',
            self::ARCHIVED     => 'Archived',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT        => 'info',
            self::PENDING      => 'secondary',
            self::REVIEW       => 'warning',
            self::NEEDS_ACTION => 'danger',
            self::PUBLISHED    => 'success',
            self::REJECTED     => 'danger',
            self::ARCHIVED     => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT        => 'heroicon-m-pencil',
            self::PENDING      => 'heroicon-m-clock',
            self::REVIEW       => 'heroicon-m-eye',
            self::NEEDS_ACTION => 'heroicon-m-cursor-arrow-rays',
            self::PUBLISHED    => 'heroicon-m-check',
            self::REJECTED     => 'heroicon-m-x-circle',
            self::ARCHIVED     => 'heroicon-m-archive-box',
        };
    }
}
