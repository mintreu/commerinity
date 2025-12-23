<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdminTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SuperAdmin = 'superadmin';
    case Ceo = 'ceo';
    case Director = 'director';
    case Manager = 'manager';
    case Lead = 'lead';
    case Executive = 'executive';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => __('admin.types.superadmin'),
            self::Ceo => __('admin.types.ceo'),
            self::Director => __('admin.types.director'),
            self::Manager => __('admin.types.manager'),
            self::Lead => __('admin.types.lead'),
            self::Executive => __('admin.types.executive'),
        };
    }

    public function getLevel(): int
    {
        return match ($this) {
            self::SuperAdmin => 0,
            self::Ceo => 1,
            self::Director => 2,
            self::Manager => 3,
            self::Lead => 4,
            self::Executive => 5,
        };
    }

    public function getDefaultProfitSharePercent(): float
    {
        return match ($this) {
            self::SuperAdmin => 0.00,
            self::Ceo => 15.00,
            self::Director => 10.00,
            self::Manager => 5.00,
            self::Lead => 3.00,
            self::Executive => 1.00,
        };
    }

    /**
     * Get the admin type that this admin can create
     */
    public function canCreate(): ?self
    {
        return match ($this) {
            self::SuperAdmin => self::Ceo,
            self::Ceo => self::Director,
            self::Director => self::Manager,
            self::Manager => self::Lead,
            self::Lead => self::Executive,
            self::Executive => null,
        };
    }

    /**
     * Get types this admin can view stats of (same level and below)
     *
     * @return array<self>
     */
    public function getVisibleTypes(): array
    {
        $level = $this->getLevel();

        return collect(self::cases())
            ->filter(fn (self $type) => $type->getLevel() >= $level)
            ->values()
            ->all();
    }

    /**
     * Check if this admin type can manage the given type
     */
    public function canManage(self $other): bool
    {
        return $this->getLevel() < $other->getLevel();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Ceo => 'warning',
            self::Director => 'primary',
            self::Manager => 'success',
            self::Lead => 'info',
            self::Executive => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SuperAdmin => 'heroicon-o-shield-check',
            self::Ceo => 'heroicon-o-star',
            self::Director => 'heroicon-o-briefcase',
            self::Manager => 'heroicon-o-user-group',
            self::Lead => 'heroicon-o-flag',
            self::Executive => 'heroicon-o-user',
        };
    }
}
