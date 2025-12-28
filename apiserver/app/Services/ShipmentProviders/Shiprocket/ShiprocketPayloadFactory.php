<?php

declare(strict_types=1);

namespace App\Services\ShipmentProviders\Shiprocket;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Shipment;
use Illuminate\Support\Collection;

class ShiprocketPayloadFactory
{
    public function __construct(protected array $config = [])
    {
        $this->config = $config ?: config('shipping.shiprocket', []);
    }

    public function buildForwardShipmentPayload(Order $order, Shipment $shipment, Collection $items): array
    {
        $order->loadMissing(['billingAddress', 'shippingAddress', 'customerable']);
        $shipment->loadMissing(['pickupAddress', 'deliveryAddress']);

        $billing = $order->billingAddress ?? $order->shippingAddress;
        $shipping = $order->shippingAddress ?? $billing;
        $pickup = $shipment->pickupAddress ?? $shipping;
        $customer = $order->customerable;

        $defaultDims = $this->config['default_dimensions_cm'] ?? [];
        $weightKg = $this->calculateWeightKg($items);

        return [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            'channel_id' => $this->config['channel_id'] ?? null,
            'billing_customer_name' => $billing?->name ?? $customer?->name,
            'billing_last_name' => '',
            'billing_address' => $billing?->address_1,
            'billing_address_2' => $billing?->address_2,
            'billing_city' => $billing?->city,
            'billing_state' => $billing?->state,
            'billing_country' => $billing?->country_code ?? 'IN',
            'billing_pincode' => $billing?->postal_code,
            'billing_email' => $customer?->email,
            'billing_phone' => $billing?->contact ?? $customer?->phone,
            'billing_alternate_phone' => $billing?->alternate_contact,
            'shipping_is_billing' => (int) ($order->billing_address_id === $order->shipping_address_id),
            'shipping_customer_name' => $shipping?->name,
            'shipping_last_name' => '',
            'shipping_address' => $shipping?->address_1,
            'shipping_address_2' => $shipping?->address_2,
            'shipping_city' => $shipping?->city,
            'shipping_state' => $shipping?->state,
            'shipping_country' => $shipping?->country_code ?? 'IN',
            'shipping_pincode' => $shipping?->postal_code,
            'shipping_email' => $customer?->email,
            'shipping_phone' => $shipping?->contact ?? $customer?->phone,
            'order_items' => $this->formatItems($items),
            'payment_method' => $shipment->cod ? 'COD' : 'Prepaid',
            'shipping_charges' => $this->paiseToRupees($order->shipping_cost),
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => $this->paiseToRupees($order->discount),
            'sub_total' => $this->paiseToRupees($order->subtotal),
            'weight' => $weightKg,
            'length' => $defaultDims['length'] ?? 10,
            'breadth' => $defaultDims['breadth'] ?? 10,
            'height' => $defaultDims['height'] ?? 5,
            'pickup_location' => $pickup?->name ?? ($this->config['pickup_code'] ?? 'Primary'),
            'customer_gstin' => null,
            'reseller_name' => $customer?->name,
            'mode' => $shipment->shipping_method === 'express' ? 'Air' : 'Surface',
            'request_pickup' => true,
        ];
    }

    public function calculateWeightKg(Collection $items): float
    {
        $defaultGrams = (int) ($this->config['default_item_weight_grams'] ?? 500);
        $totalGrams = 0;

        /** @var OrderItem $item */
        foreach ($items as $item) {
            $quantity = (int) $item->quantity;
            if ($quantity <= 0) {
                continue;
            }

            $grams = (int) ($item->product->shipping_weight_grams ?? $item->product->weight_grams ?? $defaultGrams);
            if ($grams <= 0) {
                $grams = $defaultGrams;
            }

            $totalGrams += $grams * $quantity;
        }

        if ($totalGrams <= 0) {
            $totalGrams = $defaultGrams;
        }

        return round($totalGrams / 1000, 2);
    }

    protected function formatItems(Collection $items): array
    {
        return $items->map(function (OrderItem $item) {
            return [
                'name' => $item->product_name,
                'sku' => $item->product_sku ?? 'SKU-'.$item->product_id,
                'units' => (int) $item->quantity,
                'selling_price' => $this->paiseToRupees($item->unit_price),
                'discount' => 0,
                'tax' => 0,
            ];
        })->values()->all();
    }

    protected function paiseToRupees(int $amount): float
    {
        return round($amount / 100, 2);
    }

    public function getDefaultDimensions(): array
    {
        return $this->config['default_dimensions_cm'] ?? [
            'length' => 10,
            'breadth' => 10,
            'height' => 5,
        ];
    }
}
