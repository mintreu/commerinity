<?php

namespace Mintreu\LaravelIntegration\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum IntegrationTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case PAYMENT = 'payment';
    case PAYOUT = 'payout';
    case SHIPPING = 'shipping';
    case SMS = 'sms';
    case BOOKING = 'booking';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PAYMENT => 'success',
            self::PAYOUT => 'warning',
            self::SHIPPING => 'info',
            self::SMS => 'primary',
            self::BOOKING => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PAYMENT => 'heroicon-o-credit-card',
            self::PAYOUT => 'heroicon-o-banknotes',
            self::SHIPPING => 'heroicon-o-truck',
            self::SMS => 'heroicon-o-chat-bubble-left-right',
            self::BOOKING => 'heroicon-o-calendar-days',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAYMENT => 'Payment',
            self::PAYOUT => 'Payout',
            self::SHIPPING => 'Shipping',
            self::SMS => 'SMS',
            self::BOOKING => 'Booking',
        };
    }
}
