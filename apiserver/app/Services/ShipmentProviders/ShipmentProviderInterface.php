<?php

declare(strict_types=1);

namespace App\Services\ShipmentProviders;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Shipment;
use Illuminate\Support\Collection;

interface ShipmentProviderInterface
{
    /**
     * Human-readable provider code/name (e.g., native, shiprocket).
     */
    public function getName(): string;

    /**
     * Create a shipment with the provider and return normalized shipment data.
     *
     * @return array{
     *     success: bool,
     *     status: string|null,
     *     tracking_id: string|null,
     *     tracking_data?: array|null,
     *     activities?: array<int, array<string, mixed>>|null,
     *     message?: string|null
     * }
     */
    public function createShipment(Order $order, Shipment $shipment, Collection $items): array;

    /**
     * Fetch latest tracking info from the provider.
     *
     * @return array{
     *     success: bool,
     *     status: string|null,
     *     tracking_id: string|null,
     *     tracking_data?: array|null,
     *     activities?: array<int, array<string, mixed>>|null,
     *     message?: string|null
     * }
     */
    public function trackShipment(Shipment $shipment): array;

    /**
     * Cancel the shipment at the provider, if supported.
     *
     * @return array{success: bool, message?: string|null}
     */
    public function cancelShipment(Shipment $shipment): array;
}
