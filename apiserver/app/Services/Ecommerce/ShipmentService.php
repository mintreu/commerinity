<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\ShipmentStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Shipment;
use App\Services\ShipmentProviders\NativeShipmentProvider;
use App\Services\ShipmentProviders\ShipmentProviderInterface;
use App\Services\ShipmentProviders\Shiprocket\ShiprocketApi;
use App\Services\ShipmentProviders\Shiprocket\ShiprocketPayloadFactory;
use App\Services\ShipmentProviders\Shiprocket\ShiprocketShipmentProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ShipmentService
{
    /**
     * Registry of providers keyed by code.
     *
     * @var array<string, ShipmentProviderInterface>
     */
    protected array $providers = [];

    public function __construct(
        protected ?ShiprocketApi $shiprocketApi = null,
        protected ?ShiprocketPayloadFactory $shiprocketPayloadFactory = null,
    ) {
        // Register the native provider by default.
        $this->registerProvider(new NativeShipmentProvider);

        if (config('shipping.shiprocket.enabled')) {
            $api = $this->shiprocketApi ?? new ShiprocketApi;
            $payloadFactory = $this->shiprocketPayloadFactory ?? new ShiprocketPayloadFactory;
            $shiprocket = new ShiprocketShipmentProvider($api, $payloadFactory);
            $this->registerProvider($shiprocket);
        }
    }

    public function getProvider(?string $code = null): ShipmentProviderInterface
    {
        $code = $code ?? config('shipping.default_provider', 'native');

        if (! isset($this->providers[$code])) {
            throw new InvalidArgumentException("Unknown shipment provider: {$code}");
        }

        return $this->providers[$code];
    }

    /**
     * Create a shipment for a set of order items.
     *
     * @param  array<int,int>|Collection<int,OrderItem>  $orderItems
     */
    public function createShipment(Order $order, array|Collection $orderItems, array $options = []): Shipment
    {
        return DB::transaction(function () use ($order, $orderItems, $options) {
            $items = $orderItems instanceof Collection
                ? $orderItems
                : OrderItem::query()->whereIn('id', $orderItems)->get();

            if ($items->isEmpty()) {
                throw new InvalidArgumentException('No order items provided for shipment creation.');
            }

            if ($items->pluck('order_id')->unique()->count() !== 1 || $items->first()->order_id !== $order->id) {
                throw new InvalidArgumentException('All shipment items must belong to the provided order.');
            }

            $alreadyAssigned = $items->filter(fn (OrderItem $item) => $item->shipments()->exists())->pluck('id');
            if ($alreadyAssigned->isNotEmpty()) {
                throw new RuntimeException(sprintf(
                    'Order items %s already belong to shipments.',
                    $alreadyAssigned->join(', ')
                ));
            }

            $items->loadMissing('product');

            $pickupAddressId = $options['pickup_address_id']
                ?? $items->first()->product?->preferredWarehouseAddress()?->id;
            $deliveryAddressId = $options['delivery_address_id'] ?? $order->shipping_address_id;
            $providerCode = $options['provider'] ?? config('shipping.default_provider', 'native');
            $itemQuantities = Arr::get($options, 'item_quantities', []);

            $totalQuantity = $items->sum(function (OrderItem $item) use ($itemQuantities) {
                return (int) ($itemQuantities[$item->id] ?? $item->quantity);
            });

            $shipment = Shipment::create([
                'order_id' => $order->id,
                'pickup_address_id' => $pickupAddressId,
                'delivery_address_id' => $deliveryAddressId,
                'total_quantity' => $totalQuantity,
                'status' => ShipmentStatusCast::PROCESSING->value,
                'shipping_method' => $options['shipping_method'] ?? 'standard',
                'provider' => $providerCode,
                'cod' => Arr::get($options, 'cod', false),
                'cod_amount' => Arr::get($options, 'cod_amount', 0),
                'cod_status' => Arr::get($options, 'cod_status', 'pending'),
                'charge' => Arr::get($options, 'charge', 0),
            ]);

            foreach ($items as $item) {
                $quantity = (int) ($itemQuantities[$item->id] ?? $item->quantity);
                $shipment->shipmentItems()->create([
                    'order_item_id' => $item->id,
                    'quantity' => $quantity,
                ]);
            }

            $provider = $this->getProvider($shipment->provider);
            $result = $provider->createShipment($order, $shipment, $items);

            $shipment->fill([
                'status' => $result['status'] ?? $shipment->status,
                'tracking_id' => $result['tracking_id'] ?? $shipment->tracking_id,
                'tracking_data' => $result['tracking_data'] ?? $shipment->tracking_data,
                'shipment_track_activities' => $result['activities'] ?? $shipment->shipment_track_activities,
                'provider_order_id' => $result['provider_order_id'] ?? $shipment->provider_order_id,
                'shipment_id' => $result['provider_shipment_id'] ?? $shipment->shipment_id,
                'last_update' => $result['tracking_data'] ?? $shipment->last_update,
                'last_synced_at' => now(),
            ]);

            if ($shipment->isDirty()) {
                $shipment->save();
            }

            return $shipment;
        });
    }

    /**
     * Sync shipment status from provider.
     */
    public function syncStatus(Shipment $shipment): Shipment
    {
        $provider = $this->getProvider($shipment->provider);
        $result = $provider->trackShipment($shipment);

        $shipment->fill([
            'status' => $result['status'] ?? $shipment->status,
            'tracking_id' => $result['tracking_id'] ?? $shipment->tracking_id,
            'tracking_data' => $result['tracking_data'] ?? $shipment->tracking_data,
            'shipment_track_activities' => $result['activities'] ?? $shipment->shipment_track_activities,
            'last_update' => $result['tracking_data'] ?? $shipment->last_update,
            'last_synced_at' => now(),
        ]);

        if ($shipment->isDirty()) {
            $shipment->save();
        }

        return $shipment;
    }

    /**
     * Cancel a shipment via provider, then mark locally.
     */
    public function cancelShipment(Shipment $shipment): bool
    {
        $provider = $this->getProvider($shipment->provider);
        $result = $provider->cancelShipment($shipment);

        if (! $result['success']) {
            return false;
        }

        $shipment->update([
            'status' => ShipmentStatusCast::CANCELLED->value,
            'cancelled_at' => now(),
            'shipment_track_activities' => $this->appendActivity(
                $shipment->shipment_track_activities,
                [
                    'date' => now()->toDateTimeString(),
                    'status' => 'CANCELLED',
                    'activity' => 'Shipment cancelled',
                    'location' => $shipment->pickupAddress?->city ?? 'System',
                ]
            ),
        ]);

        return true;
    }

    /**
     * Mark shipment as shipped
     */
    public function markAsShipped(Shipment $shipment): bool
    {
        $shipment->update([
            'status' => ShipmentStatusCast::IN_TRANSIT->value,
            'shipped_at' => now(),
            'shipment_track_activities' => $this->appendActivity(
                $shipment->shipment_track_activities,
                [
                    'date' => now()->toDateTimeString(),
                    'status' => 'SHIPPED',
                    'activity' => 'Shipment dispatched',
                    'location' => $shipment->pickupAddress?->city ?? 'Warehouse',
                ]
            ),
        ]);

        return true;
    }

    /**
     * Mark shipment as delivered
     */
    public function markAsDelivered(Shipment $shipment): bool
    {
        $shipment->update([
            'status' => ShipmentStatusCast::DELIVERED->value,
            'delivered_at' => now(),
            'shipment_track_activities' => $this->appendActivity(
                $shipment->shipment_track_activities,
                [
                    'date' => now()->toDateTimeString(),
                    'status' => 'DELIVERED',
                    'activity' => 'Shipment delivered',
                    'location' => $shipment->deliveryAddress?->city ?? 'Destination',
                ]
            ),
        ]);

        return true;
    }

    private function appendActivity(?array $activities, array $activity): array
    {
        $activities = $activities ?? [];
        $activities[] = $activity;

        return $activities;
    }

    protected function registerProvider(ShipmentProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }
}
