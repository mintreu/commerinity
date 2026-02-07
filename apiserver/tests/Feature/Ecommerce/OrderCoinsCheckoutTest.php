<?php

use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Product;
use App\Models\User;
use App\Services\UserServices\UserWalletService;
use Database\Factories\AddressFactory;
use Database\Factories\Ecommerce\ProductStockFactory;

beforeEach(function () {
    $this->artisan('route:clear');
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);
    config()->set('wallet.points_conversion_rate', 10);
});

it('processes checkout via coins and records coins on order and items', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Coin Category',
        'url' => 'coin-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
        'reward_points' => 15,
    ]);

    $shippingAddress = AddressFactory::new()->forUser($user)->create();
    $stockAddress = AddressFactory::new()->warehouse()->create();

    ProductStockFactory::new()->create([
        'product_id' => $product->id,
        'address_id' => $stockAddress->id,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 2,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $walletService = new UserWalletService();
    $wallet = $walletService->getOrCreateWallet($user);
    $wallet->update(['points' => 5000]);

    $response = $this->actingAs($user)->postJson('/api/order/checkout', [
        'payment_method' => 'coins',
        'shipping_address_id' => $shippingAddress->uuid,
        'billing_address_id' => $shippingAddress->uuid,
        'billing_is_shipping' => true,
        'gift' => false,
    ]);

    if ($response->getStatusCode() !== 201) {
        fwrite(STDERR, $response->getContent().PHP_EOL);
    }

    $response->assertCreated()
        ->assertJsonPath('success', true);

    $order = Order::forCustomer($user)->latest('id')->first();
    expect($order)->not->toBeNull();
    expect($order->total_coins)->toBe(30);

    $orderItem = $order->items()->first();
    expect($orderItem)->not->toBeNull();
    expect($orderItem->total_coins)->toBe(30);

    $wallet->refresh();
    expect($wallet->points)->toBeLessThan(5000);
});
