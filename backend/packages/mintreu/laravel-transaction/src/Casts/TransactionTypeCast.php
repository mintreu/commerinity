<?php

namespace Mintreu\LaravelTransaction\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionTypeCast: string implements HasLabel, HasIcon, HasColor
{
    case CREDITED = 'credited';
    case DEBITED = 'debited';
    case REFUNDED = 'refunded';
    case CHARGEBACK = 'chargeback';
    case ADJUSTMENT = 'adjustment';
    case HOLD = 'hold';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREDITED => 'success',
            self::DEBITED => 'danger',
            self::REFUNDED => 'info',
            self::CHARGEBACK => 'warning',
            self::ADJUSTMENT => 'secondary',
            self::HOLD => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CREDITED => 'heroicon-o-arrow-up-circle',
            self::DEBITED => 'heroicon-o-arrow-down-circle',
            self::REFUNDED => 'heroicon-o-arrow-path',
            self::CHARGEBACK => 'heroicon-o-exclamation-circle',
            self::ADJUSTMENT => 'heroicon-o-cog-6-tooth',
            self::HOLD => 'heroicon-o-pause-circle',
        };
    }

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::CREDITED => 'Funds added to your wallet successfully.',
            self::DEBITED => 'Funds deducted from your wallet.',
            self::REFUNDED => 'Refund processed to your wallet.',
            self::CHARGEBACK => 'Transaction reversed due to dispute.',
            self::ADJUSTMENT => 'Wallet balance manually adjusted.',
            self::HOLD => 'Funds are on hold and unavailable for use.',
        };
    }
}

