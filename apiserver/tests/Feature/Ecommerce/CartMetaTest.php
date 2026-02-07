<?php

use App\Casts\UserTypeCast;
use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\Ecommerce\VoucherManager;
use Database\Factories\AddressFactory;
use Database\Factories\Ecommerce\ProductStockFactory;

it('returns cart meta with correct totals and line details', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $user = User::factory()->create([
        'type' => UserTypeCast::MEMBER->value,
    ]);

    $category = Category::create([
        'name' => 'Cart Category',
        'url' => 'cart-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 35000,
        'bv' => 10,
        'pv' => 5,
        'reward_points' => 2,
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

    $cart = new CartService($user);
    $meta = $cart->getMeta($shippingAddress, true);

    expect($meta['summary']['sub_total'])->toBe(70000);
    expect($meta['summary']['tax'])->toBe(0);
    expect($meta['summary']['discount'])->toBe(0);
    expect($meta['summary']['shipping_cost'])->toBe(0);
    expect($meta['summary']['total'])->toBe(70000);

    $items = $meta['items'];
    expect(count($items))->toBe(1);
    expect($items[0]['requested_quantity'])->toBe(2);
    expect($items[0]['allocated_quantity'])->toBe(2);
    expect($items[0]['item_total'])->toBe(70000);
    expect($items[0]['bv'])->toBe(20);
    expect($items[0]['pv'])->toBe(10);
    expect($items[0]['reward_points'])->toBe(4);
});

it('applies cart-level voucher discount in meta summary', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $user = User::factory()->create([
        'type' => UserTypeCast::MEMBER->value,
    ]);

    $category = Category::create([
        'name' => 'Voucher Category',
        'url' => 'voucher-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 35000,
        'bv' => 10,
        'pv' => 5,
        'reward_points' => 2,
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

    $voucher = VoucherManager::create([
        'name' => 'Cart Voucher',
        'description' => 'Cart voucher',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(7)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 5,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 60000],
        ],
        'end_other_rules' => false,
        'action_type' => \App\Casts\VoucherActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 1,
        'discount_step' => null,
        'apply_to_shipping' => false,
        'free_shipping' => false,
        'min_cart_value' => 0,
        'min_quantity' => 0,
        'sort_order' => 1,
    ]);

    $code = $voucher->primaryCode?->code ?? $voucher->codes()->first()?->code;

    $cart = new CartService($user);
    $cart->setCouponCode($code);

    $meta = $cart->getMeta($shippingAddress, true);

    expect($meta['summary']['sub_total'])->toBe(70000);
    expect($meta['summary']['discount'])->toBe(7000);
    expect($meta['summary']['total'])->toBe(63000);
});
