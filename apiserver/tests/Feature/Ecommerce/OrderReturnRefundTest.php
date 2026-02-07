<?php

declare(strict_types=1);

use App\Casts\ShipmentStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\Shipment;
use App\Models\Ecommerce\ShipmentItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows return request after 24h and within return window', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'walletable_type' => User::class,
        'walletable_id' => $user->id,
    ]);

    $shippingAddress = Address::factory()->for($user, 'addressable')->create();
    $pickupAddress = Address::factory()->warehouse()->create();

    $product = Product::factory()->create([
        'is_returnable' => true,
        'return_days' => 7,
    ]);

    $stock = ProductStock::factory()->create([
        'product_id' => $product->id,
        'address_id' => $pickupAddress->id,
        'init_quantity' => 10,
        'sold_quantity' => 1,
    ]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => 'delivered',
        'subtotal' => 10000,
        'tax' => 1800,
        'total' => 11800,
        'shipping_address_id' => $shippingAddress->id,
        'quantity' => 1,
        'delivered_at' => now()->subDays(1),
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'stock_id' => $stock->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
    ]);

    $shipment = Shipment::create([
        'order_id' => $order->id,
        'pickup_address_id' => $pickupAddress->id,
        'delivery_address_id' => $shippingAddress->id,
        'total_quantity' => 1,
        'status' => ShipmentStatusCast::DELIVERED->value,
        'provider' => 'native',
        'shipped_at' => now()->subDays(2),
        'delivered_at' => now()->subDays(1),
    ]);

    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->postJson('/api/order/return', [
        'order_item_uuid' => $orderItem->uuid,
        'reason' => 'Damaged item',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $returnShipment = Shipment::where('delivery_address_id', $pickupAddress->id)
        ->where('pickup_address_id', $shippingAddress->id)
        ->first();

    expect($returnShipment)->not->toBeNull();
    expect($returnShipment->status->value)->toBe(ShipmentStatusCast::RETURNING->value);
});

it('rejects return request before 24 hours', function () {
    $user = User::factory()->create();
    $shippingAddress = Address::factory()->for($user, 'addressable')->create();
    $pickupAddress = Address::factory()->warehouse()->create();

    $product = Product::factory()->create([
        'is_returnable' => true,
        'return_days' => 7,
    ]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => 'delivered',
        'subtotal' => 10000,
        'tax' => 1800,
        'total' => 11800,
        'shipping_address_id' => $shippingAddress->id,
        'quantity' => 1,
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
    ]);

    $shipment = Shipment::create([
        'order_id' => $order->id,
        'pickup_address_id' => $pickupAddress->id,
        'delivery_address_id' => $shippingAddress->id,
        'total_quantity' => 1,
        'status' => ShipmentStatusCast::DELIVERED->value,
        'provider' => 'native',
        'shipped_at' => now()->subHours(5),
    ]);

    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->postJson('/api/order/return', [
        'order_item_uuid' => $orderItem->uuid,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('creates pending refund transaction after return completed', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'walletable_type' => User::class,
        'walletable_id' => $user->id,
    ]);

    $shippingAddress = Address::factory()->for($user, 'addressable')->create();
    $pickupAddress = Address::factory()->warehouse()->create();

    $product = Product::factory()->create([
        'is_returnable' => true,
        'return_days' => 7,
    ]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => 'delivered',
        'subtotal' => 10000,
        'tax' => 1800,
        'total' => 11800,
        'shipping_address_id' => $shippingAddress->id,
        'quantity' => 1,
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
    ]);

    $shipment = Shipment::create([
        'order_id' => $order->id,
        'pickup_address_id' => $pickupAddress->id,
        'delivery_address_id' => $shippingAddress->id,
        'total_quantity' => 1,
        'status' => ShipmentStatusCast::RETURNED->value,
        'provider' => 'native',
        'shipped_at' => now()->subDays(2),
        'returned_at' => now()->subDay(),
    ]);

    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 1,
    ]);

    $transaction = Transaction::create([
        'wallet_id' => $wallet->id,
        'transactionable_type' => Order::class,
        'transactionable_id' => $order->id,
        'type' => TransactionTypeCast::DEBIT,
        'status' => TransactionStatusCast::COMPLETED,
        'amount' => 11800,
        'purpose' => 'Order Payment',
        'verified' => true,
        'verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/api/order/refund', [
        'order_item_uuid' => $orderItem->uuid,
        'reason' => 'Return completed',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', TransactionStatusCast::PENDING->value);

    $refund = Transaction::where('type', TransactionTypeCast::REFUND)->first();
    expect($refund)->not->toBeNull();
    expect($refund->status)->toBe(TransactionStatusCast::PENDING);
});
