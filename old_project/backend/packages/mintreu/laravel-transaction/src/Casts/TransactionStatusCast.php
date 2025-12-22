<?php

namespace Mintreu\LaravelTransaction\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatusCast: string implements HasLabel, HasIcon, HasColor
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case ON_HOLD = 'on_hold';
    case CHARGEBACK = 'chargeback';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
            self::REFUNDED => 'secondary',
            self::ON_HOLD => 'warning',
            self::CHARGEBACK => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PROCESSING => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-minus-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::ON_HOLD => 'heroicon-o-pause-circle',
            self::CHARGEBACK => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getLabel(): ?string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}

