<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\ShipmentProviders\Shiprocket;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Shipment;
use App\Services\IntegrationServices\ShipmentProviders\ShipmentProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShiprocketShipmentProvider implements ShipmentProviderInterface
{
    public function __construct(
        protected ShiprocketApi $api,
        protected ShiprocketPayloadFactory $payloadFactory,
    ) {}

    public function getName(): string
    {
        return 'shiprocket';
    }

    public function createShipment(Order $order, Shipment $shipment, Collection $items): array
    {
        try {
            $payload = $this->payloadFactory->buildForwardShipmentPayload($order, $shipment, $items);
            $response = $this->api->createForwardShipment($payload);

            $payloadData = $response['payload'] ?? [];
            $status = ShiprocketStatusMapper::map(
                $payloadData['current_status'] ?? null,
                $payloadData['current_status_id'] ?? null
            );

            return [
                'success' => (bool) ($response['status'] ?? false),
                'status' => $status,
                'tracking_id' => $payloadData['awb_code'] ?? null,
                'tracking_data' => $payloadData,
                'activities' => $payloadData['scans'] ?? null,
                'provider_order_id' => $payloadData['order_id'] ?? null,
                'provider_shipment_id' => $payloadData['shipment_id'] ?? null,
                'message' => $response['message'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::error('Shiprocket create shipment failed', [
                'order_id' => $order->id,
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function trackShipment(Shipment $shipment): array
    {
        try {
            $response = $shipment->tracking_id
                ? $this->api->trackByAwb($shipment->tracking_id)
                : $this->api->trackByShipmentId($shipment->shipment_id ?? $shipment->provider_order_id);

            $tracking = $response['tracking_data'] ?? [];

            return [
                'success' => (int) ($response['status'] ?? 0) === 200,
                'status' => ShiprocketStatusMapper::map(
                    $tracking['shipment_status'] ?? null,
                    $tracking['shipment_status_id'] ?? null
                ),
                'tracking_id' => $response['awb'] ?? $shipment->tracking_id,
                'tracking_data' => $tracking,
                'activities' => $tracking['shipment_track'] ?? $tracking['scans'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::warning('Shiprocket tracking failed', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function cancelShipment(Shipment $shipment): array
    {
        $id = $shipment->shipment_id ?? $shipment->provider_order_id;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Shiprocket shipment identifier missing.',
            ];
        }

        try {
            $response = $this->api->cancelShipment($id);

            return [
                'success' => (bool) ($response['status'] ?? false),
                'message' => $response['message'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::error('Shiprocket cancel shipment failed', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
