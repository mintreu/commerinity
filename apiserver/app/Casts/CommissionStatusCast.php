<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Commission Status Cast
 *
 * Tracks the lifecycle status of a commission from calculation to payout.
 */
final class CommissionStatusCast implements CastsAttributes
{
    // ========================================
    // STATUS VALUES
    // ========================================

    /** Calculated, awaiting approval */
    public const PENDING = 'pending';

    /** Approved, ready for payout */
    public const APPROVED = 'approved';

    /** Payout in progress */
    public const PROCESSING = 'processing';

    /** Credited to wallet */
    public const PAID = 'paid';

    /** On hold (compliance, verification) */
    public const HELD = 'held';

    /** Cancelled before payout */
    public const CANCELLED = 'cancelled';

    /** Clawed back after payout */
    public const REVERSED = 'reversed';

    /**
     * All valid statuses
     */
    public static function values(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::PROCESSING,
            self::PAID,
            self::HELD,
            self::CANCELLED,
            self::REVERSED,
        ];
    }

    /**
     * Get human-readable labels
     */
    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::PROCESSING => 'Processing',
            self::PAID => 'Paid',
            self::HELD => 'On Hold',
            self::CANCELLED => 'Cancelled',
            self::REVERSED => 'Reversed',
        ];
    }

    /**
     * Get label for a specific status
     */
    public static function label(string $status): string
    {
        return self::labels()[$status] ?? $status;
    }

    /**
     * Get color for UI display
     */
    public static function colors(): array
    {
        return [
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::PROCESSING => 'primary',
            self::PAID => 'success',
            self::HELD => 'warning',
            self::CANCELLED => 'secondary',
            self::REVERSED => 'error',
        ];
    }

    /**
     * Get color for specific status
     */
    public function color(): string
    {
        return self::colors()[$this->value] ?? 'secondary';
    }

    /**
     * Check if commission can be approved
     */
    public function canBeApproved(): bool
    {
        return in_array($this->value, [self::PENDING, self::HELD], true);
    }

    /**
     * Check if commission can be paid
     */
    public function canBePaid(): bool
    {
        return $this->value === self::APPROVED;
    }

    /**
     * Check if commission can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->value, [self::PENDING, self::APPROVED, self::HELD], true);
    }

    /**
     * Check if commission can be reversed
     */
    public function canBeReversed(): bool
    {
        return $this->value === self::PAID;
    }

    /**
     * Check if commission can be put on hold
     */
    public function canBeHeld(): bool
    {
        return in_array($this->value, [self::PENDING, self::APPROVED], true);
    }

    /**
     * Check if commission is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this->value, [self::PAID, self::CANCELLED, self::REVERSED], true);
    }

    /**
     * Check if commission affects balance
     */
    public function affectsBalance(): bool
    {
        return $this->value === self::PAID;
    }

    // ========================================
    // CAST IMPLEMENTATION
    // ========================================

    private string $value;

    public function __construct(string $value = self::PENDING)
    {
        if (! in_array($value, self::values(), true)) {
            throw new InvalidArgumentException("Invalid commission status: {$value}");
        }
        $this->value = $value;
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): self
    {
        return new self($value ?? self::PENDING);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof self) {
            return $value->value;
        }

        if (is_string($value) && in_array($value, self::values(), true)) {
            return $value;
        }

        throw new InvalidArgumentException("Invalid commission status: {$value}");
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
