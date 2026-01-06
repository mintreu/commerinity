<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\ShipmentProviders;

use App\Casts\ShipmentStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Shipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class NativeShipmentProvider implements ShipmentProviderInterface
{
    public function getName(): string
    {
        return 'native';
    }

    public function createShipment(Order $order, Shipment $shipment, Collection $items): array
    {
        $trackingId = $this->generateTrackingId($order, $shipment);
        $now = Date::now();

        return [
            'success' => true,
            'status' => ShipmentStatusCast::PROCESSING->value,
            'tracking_id' => $trackingId,
            'tracking_data' => [
                'courier_name' => 'Native Logistics',
                'origin' => $shipment->pickupAddress?->city,
                'destination' => $shipment->deliveryAddress?->city,
                'etd' => $now->copy()->addDays(3)->toISOString(),
            ],
            'activities' => [
                [
                    'date' => $now->toDateTimeString(),
                    'status' => 'INIT',
                    'activity' => 'Shipment created',
                    'location' => $shipment->pickupAddress?->city ?? 'Warehouse',
                ],
            ],
        ];
    }

    public function trackShipment(Shipment $shipment): array
    {
        return [
            'success' => true,
            'status' => $shipment->status?->value ?? ShipmentStatusCast::PROCESSING->value,
            'tracking_id' => $shipment->tracking_id,
            'tracking_data' => $shipment->tracking_data,
            'activities' => $shipment->shipment_track_activities,
        ];
    }

    public function cancelShipment(Shipment $shipment): array
    {
        return [
            'success' => true,
            'message' => 'Cancelled locally',
        ];
    }

    private function generateTrackingId(Order $order, Shipment $shipment): string
    {
        return sprintf(
            'SHP-%s-%s',
            $order->order_number ?? $order->getRouteKey(),
            Str::padLeft((string) $shipment->getKey(), 4, '0')
        );
    }
}
