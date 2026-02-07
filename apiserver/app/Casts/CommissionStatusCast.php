<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Commission Status Enum (Filament v4 friendly)
 *
 * Use in Filament:
 * - Select::make('status')->options(CommissionStatusCast::class)
 * - TextEntry::make('status')->badge()
 *
 * Use in Eloquent:
 * - protected $casts = ['status' => CommissionStatusCast::class];
 */
enum CommissionStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case HELD = 'held';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';

    /* -------------------------------------------------
     | Filament UI
     -------------------------------------------------*/

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::PROCESSING => 'Processing',
            self::PAID => 'Paid',
            self::HELD => 'On Hold',
            self::CANCELLED => 'Cancelled',
            self::REVERSED => 'Reversed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => Color::Blue,     // "info" vibe
            self::PROCESSING => 'primary',
            self::PAID => 'success',
            self::HELD => Color::Amber,        // hold = attention
            self::CANCELLED => Color::Gray,    // neutral
            self::REVERSED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-badge',
            self::PROCESSING => 'heroicon-o-arrow-path',
            self::PAID => 'heroicon-o-banknotes',
            self::HELD => 'heroicon-o-pause-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REVERSED => 'heroicon-o-arrow-uturn-left',
        };
    }

    /* -------------------------------------------------
     | Helpers (same behavior as your old class)
     -------------------------------------------------*/

    public function canBeApproved(): bool
    {
        return in_array($this, [self::PENDING, self::HELD], true);
    }

    public function canBePaid(): bool
    {
        return $this === self::APPROVED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::APPROVED, self::HELD], true);
    }

    public function canBeReversed(): bool
    {
        return $this === self::PAID;
    }

    public function canBeHeld(): bool
    {
        return in_array($this, [self::PENDING, self::APPROVED], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::CANCELLED, self::REVERSED], true);
    }

    public function affectsBalance(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Get all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    /**
     * Get labels keyed by value.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->getLabel();
        }

        return $labels;
    }
}
