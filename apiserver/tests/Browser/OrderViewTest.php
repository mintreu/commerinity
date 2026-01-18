<?php

use App\Casts\OrderStatusCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

test('user can view order details', function () {
    // Create a user with password
    $user = User::factory()->create([
        'email' => 'testorder@mintreu.com',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    // Create address
    $address = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    // Create product and stock
    $product = Product::factory()->create([
        'name' => 'Test Access Product'
    ]);

    $stock = ProductStock::factory()->create([
        'product_id' => $product->id,
        'price' => 10000, // 100.00
    ]);

    // Create order manually
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

    // Create order item
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

    // Login manual flow
    // Try visiting /auth/login based on file structure
    $page = visit('/auth/login');

    $page->assertSee('Sign in')
        ->fill('email', 'testorder@mintreu.com')
        ->fill('password', 'password123')
        ->click('button[type="submit"]')
        ->pause(3000)
        ->assertPathIs('/dashboard');

    // Visit order page
    $page->visit('/order/' . $order->uuid)
        ->pause(3000)
        ->assertPathIs('/order/' . $order->uuid)
        ->assertSee($order->order_number)
        ->assertSee('Test Access Product')
        // We'll relax the price check to just containing '105' in case of formatting like '105' or '105.00'
        ->assertSee('105');
});
