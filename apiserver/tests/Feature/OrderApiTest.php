<?php

use App\Casts\OrderStatusCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;

test('order api returns standard image structure', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    $product = Product::factory()->create();
    $stock = ProductStock::factory()->create(['product_id' => $product->id]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => OrderStatusCast::CONFIRMED,
        'subtotal' => 1000,
        'shipping_cost' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 1100,
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
        'unit_price' => 1000,
        'total_price' => 1000,
        'tax' => 0,
    ]);

    $this->actingAs($user);

    $response = $this->getJson("/api/orders/{$order->uuid}");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'items' => [
                    '*' => [
                        'id',
                        'product_name',
                        'image'
                    ]
                ]
            ]
        ]);
});
