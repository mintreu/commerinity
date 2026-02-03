<?php

declare(strict_types=1);

use App\Casts\OrderStatusCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can retrieve order details via the api', function () {
    $user = User::factory()->create([
        'email' => 'testorder@mintreu.com',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $address = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    $product = Product::factory()->create([
        'name' => 'Test Access Product',
    ]);

    $stock = ProductStock::factory()->create([
        'product_id' => $product->id,
        'price' => 10000,
    ]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => OrderStatusCast::CONFIRMED,
        'subtotal' => 10000,
        'shipping_cost' => 500,
        'tax' => 0,
        'discount' => 0,
        'total' => 10500,
        'shipping_address_id' => $address->id,
        'billing_address_id' => $address->id,
        'payment_success' => true,
        'quantity' => 1,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_stock_id' => $stock->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
        'tax' => 0,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/orders/{$order->uuid}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.uuid', $order->uuid)
        ->assertJsonPath('data.order_number', $order->order_number)
        ->assertJsonPath('data.status', OrderStatusCast::CONFIRMED->value)
        ->assertJsonPath('data.payment_status', 'paid')
        ->assertJsonPath('data.total', 10500)
        ->assertJsonPath('data.total_formatted', MoneyService::format($order->total))
        ->assertJsonPath('data.items.0.product_name', $product->name)
        ->assertJsonPath('data.items.0.unit_price', 10000)
        ->assertJsonPath('data.items.0.subtotal', 10000)
        ->assertJsonPath('data.items.0.subtotal_formatted', MoneyService::format(10000));
});
