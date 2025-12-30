<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Order Status Enum Cast
 *
 * Order Flow:
 * PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED → COMPLETED
 *
 * - DELIVERED: Order delivered to customer, return period starts
 * - COMPLETED: Return period ended, order finalized, Affiliate commissions triggered
 */
enum OrderStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::CONFIRMED => 'warning',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'info',
            self::DELIVERED => 'success',
            self::COMPLETED => 'emerald',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-m-clock',
            self::CONFIRMED => 'heroicon-m-check-badge',
            self::PROCESSING => 'heroicon-m-cog-8-tooth',
            self::SHIPPED => 'heroicon-m-truck',
            self::DELIVERED => 'heroicon-m-inbox-arrow-down',
            self::COMPLETED => 'heroicon-m-check-badge',
            self::CANCELLED => 'heroicon-m-x-circle',
            self::REFUNDED => 'heroicon-m-currency-rupee',
        };
    }

    /**
     * Check if this status allows Affiliate commission processing
     */
    public function canTriggerCommission(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if this status is a terminal state (no more transitions)
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::REFUNDED], true);
    }

    /**
     * Check if order can be cancelled from this status
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED], true);
    }

    /**
     * Check if order is in return period
     */
    public function isInReturnPeriod(): bool
    {
        return $this === self::DELIVERED;
    }
}
