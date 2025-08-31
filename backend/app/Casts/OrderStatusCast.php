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
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match($this) {
            self::PROCESSING => 'heroicon-o-refresh',       // Outline refresh icon
            self::PENDING => 'heroicon-o-clock',           // Outline clock icon
            self::PAYMENT_FAILED => 'heroicon-o-x-circle', // Outline X circle icon
            self::CONFIRM => 'heroicon-o-check-circle',    // Outline check circle icon
            self::REVIEW => 'heroicon-o-eye',             // Outline eye icon
            self::ACCEPTED => 'heroicon-o-check',         // Outline check icon
            self::READY_TO_SHIP => 'heroicon-o-truck',    // Outline truck icon
            self::IN_TRANSIT => 'heroicon-o-arrow-path',  // Outline arrow path (representing transit)
            self::COMPLETED => 'heroicon-o-badge-check',  // Outline badge check icon
            self::CANCELLED => 'heroicon-o-ban',          // Outline ban icon
            self::REFUNDED => 'heroicon-o-arrow-uturn-left', // Outline u-turn arrow icon
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
        };
    }
}

