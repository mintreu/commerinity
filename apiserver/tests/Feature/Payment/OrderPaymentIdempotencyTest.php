<?php

declare(strict_types=1);

use App\Casts\OrderStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Events\PaymentCompleted;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order payment completion is idempotent for duplicate events', function () {
    $user = User::factory()->create();
    $shippingAddress = Address::factory()->for($user, 'addressable')->create();
    $pickupAddress = Address::factory()->warehouse()->create();

    $product = Product::factory()->create();
    $stock = ProductStock::factory()->create([
        'product_id' => $product->id,
        'address_id' => $pickupAddress->id,
        'init_quantity' => 20,
        'sold_quantity' => 0,
    ]);

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $user->id,
        'status' => OrderStatusCast::PENDING->value,
        'subtotal' => 10000,
        'total' => 11800,
        'shipping_address_id' => $shippingAddress->id,
        'quantity' => 1,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
    ]);

    $transaction = Transaction::create([
        'uuid' => 'TXN-ORDER-IDEMP-' . fake()->randomNumber(6),
        'transactionable_type' => Order::class,
        'transactionable_id' => $order->id,
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 11800,
        'purpose' => 'Order Payment',
        'payment_method' => 'cashfree',
    ]);

    $transaction->update([
        'status' => TransactionStatusCast::COMPLETED,
        'verified' => true,
        'verified_at' => now(),
    ]);

    event(new PaymentCompleted($transaction));
    event(new PaymentCompleted($transaction->fresh()));

    expect($order->fresh()->payment_success)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatusCast::CONFIRMED);
    expect($stock->fresh()->sold_quantity)->toBe(1);
    expect($order->fresh()->shipments()->count())->toBe(1);
});
