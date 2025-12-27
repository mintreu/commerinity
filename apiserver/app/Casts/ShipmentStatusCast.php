<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ShipmentStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case PROCESSING = 'processing';
    case REVIEW = 'review';
    case PACKING = 'packing';
    case READY_TO_SHIP = 'ready_to_ship';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';
    case RETURNING = 'returning';

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'Processing',
            self::REVIEW => 'Review',
            self::PACKING => 'Packing',
            self::READY_TO_SHIP => 'Ready To Ship',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::RETURNED => 'Returned',
            self::RETURNING => 'Returning',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PROCESSING => 'primary',
            self::REVIEW => 'warning',
            self::PACKING => 'gray',
            self::READY_TO_SHIP => 'info',
            self::IN_TRANSIT => 'info',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::RETURNED => 'danger',
            self::RETURNING => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'heroicon-m-cog-6-tooth',
            self::REVIEW => 'heroicon-m-magnifying-glass',
            self::PACKING => 'heroicon-m-cube',
            self::READY_TO_SHIP => 'heroicon-m-truck',
            self::IN_TRANSIT => 'heroicon-m-truck',
            self::DELIVERED => 'heroicon-m-check-badge',
            self::CANCELLED => 'heroicon-m-x-circle',
            self::RETURNED => 'heroicon-m-arrow-uturn-left',
            self::RETURNING => 'heroicon-m-arrow-path',
        };
    }
}
