<?php

declare(strict_types=1);

namespace App\Services\ShipmentProviders\Shiprocket;

use App\Casts\ShipmentStatusCast;

class ShiprocketStatusMapper
{
    public static function map(?string $status, ?int $statusId = null): string
    {
        $status = strtoupper((string) $status);

        return match (true) {
            in_array($status, ['DELIVERED', 'DELIVERED TO BUYER'], true) || $statusId === 7 => ShipmentStatusCast::DELIVERED->value,
            in_array($status, ['IN TRANSIT', 'SHIPPED', 'REACHED AT DESTINATION'], true) || in_array($statusId, [6, 18, 38], true) => ShipmentStatusCast::IN_TRANSIT->value,
            in_array($status, ['PICKED UP', 'READY TO SHIP'], true) || $statusId === 42 => ShipmentStatusCast::READY_TO_SHIP->value,
            in_array($status, ['OUT FOR DELIVERY'], true) || $statusId === 17 => ShipmentStatusCast::READY_TO_SHIP->value,
            in_array($status, ['CANCELLED'], true) || $statusId === 8 => ShipmentStatusCast::CANCELLED->value,
            in_array($status, ['RETURNED', 'RTO DELIVERED'], true) || in_array($statusId, [10, 24], true) => ShipmentStatusCast::RETURNED->value,
            default => ShipmentStatusCast::PROCESSING->value,
        };
    }
}
