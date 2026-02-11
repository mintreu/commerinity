<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
    case REFUND = 'refund';
    case CHARGEBACK = 'chargeback';
    case ADJUSTMENT = 'adjustment';
    case HOLD = 'hold';
    case RELEASE = 'release';
    case WITHDRAWAL = 'withdrawal';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREDIT => 'Credit',
            self::DEBIT => 'Debit',
            self::REFUND => 'Refund',
            self::CHARGEBACK => 'Chargeback',
            self::ADJUSTMENT => 'Adjustment',
            self::HOLD => 'Hold',
            self::RELEASE => 'Release',
            self::WITHDRAWAL => 'Withdrawal',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CREDIT => 'success',
            self::DEBIT => 'danger',
            self::REFUND => 'info',
            self::CHARGEBACK => 'warning',
            self::ADJUSTMENT => 'gray',
            self::HOLD => 'warning',
            self::RELEASE => 'success',
            self::WITHDRAWAL => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CREDIT => 'heroicon-o-arrow-down-circle',
            self::DEBIT => 'heroicon-o-arrow-up-circle',
            self::REFUND => 'heroicon-o-arrow-uturn-left',
            self::CHARGEBACK => 'heroicon-o-exclamation-triangle',
            self::ADJUSTMENT => 'heroicon-o-adjustments-horizontal',
            self::HOLD => 'heroicon-o-pause-circle',
            self::RELEASE => 'heroicon-o-play-circle',
            self::WITHDRAWAL => 'heroicon-o-currency-rupee',
        };
    }

    /**
     * Check if this type increases balance.
     */
    public function isPositive(): bool
    {
        return in_array($this, [self::CREDIT, self::REFUND, self::RELEASE]);
    }

    /**
     * Check if this type decreases balance.
     */
    public function isNegative(): bool
    {
        return in_array($this, [self::DEBIT, self::CHARGEBACK, self::HOLD, self::WITHDRAWAL]);
    }
}
