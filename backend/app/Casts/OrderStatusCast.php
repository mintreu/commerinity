<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatusCast: string implements HasLabel, HasIcon, HasColor
{
    case PROCESSING = 'processing';
    case PENDING = 'pending';
    case PAYMENT_FAILED = 'payment_failed';
    case CONFIRM = 'confirm';
    case REVIEW = 'review';
    case ACCEPTED = 'accepted';
    case READY_TO_SHIP = 'ready_to_ship';
    case IN_TRANSIT = 'in_transit';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case RETURN = 'return';  // NEW: Return case

    /**
     * @return string|array|null
     */
    public function getColor(): string|array|null
    {
        return match($this) {
            self::PROCESSING => 'yellow',
            self::PENDING => 'blue',
            self::PAYMENT_FAILED => 'red',
            self::CONFIRM => 'green',
            self::REVIEW => 'purple',
            self::ACCEPTED => 'orange',
            self::READY_TO_SHIP => 'teal',
            self::IN_TRANSIT => 'grey',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'blue',
            self::RETURN => 'orange',  // NEW: Return color
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match($this) {
            self::PROCESSING => 'heroicon-o-refresh',
            self::PENDING => 'heroicon-o-clock',
            self::PAYMENT_FAILED => 'heroicon-o-x-circle',
            self::CONFIRM => 'heroicon-o-check-circle',
            self::REVIEW => 'heroicon-o-eye',
            self::ACCEPTED => 'heroicon-o-check',
            self::READY_TO_SHIP => 'heroicon-o-truck',
            self::IN_TRANSIT => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-badge-check',
            self::CANCELLED => 'heroicon-o-ban',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::RETURN => 'heroicon-o-arrow-uturn-down',  // NEW: Return icon
        };
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return match($this) {
            self::PROCESSING => 'Processing',
            self::PENDING => 'Pending',
            self::PAYMENT_FAILED => 'Payment Failed!',
            self::CONFIRM => 'Confirm',
            self::REVIEW => 'Review',
            self::ACCEPTED => 'Accepted',
            self::READY_TO_SHIP => 'Ready To Ship',
            self::IN_TRANSIT => 'In Transit',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::RETURN => 'Return Initiated',  // NEW: Return label
        };
    }
}
