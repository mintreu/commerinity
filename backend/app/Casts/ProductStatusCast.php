<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'Draft';
    case REVIEW = 'review';
    case NEEDS_ACTION = 'needs_action';
    case PUBLISHED = 'Published';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::REVIEW => 'Review',
            self::NEEDS_ACTION => 'Needs Action',
            self::PUBLISHED => 'Published',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'info',
            self::REVIEW => 'warning',
            self::NEEDS_ACTION => 'danger',
            self::PUBLISHED => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-m-pencil',
            self::REVIEW => 'heroicon-m-eye',
            self::NEEDS_ACTION => 'heroicon-m-cursor-arrow-rays',
            self::PUBLISHED => 'heroicon-m-check',
        };
    }
}
