<?php

use App\Casts\UserTypeCast;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\User;
use App\Services\Ecommerce\VoucherManager;
use Database\Factories\AddressFactory;
use Database\Factories\Ecommerce\ProductStockFactory;

beforeEach(function () {
    $this->artisan('route:clear');
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);
});

it('returns cart meta on cart index api', function () {
    $user = User::factory()->create([
        'type' => UserTypeCast::MEMBER->value,
    ]);

    $category = Category::create([
        'name' => 'Api Cart Category',
        'url' => 'api-cart-category',
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

    $response = $this->actingAs($user)->getJson('/api/cart?shipping_address_id='.$shippingAddress->uuid);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.summary.sub_total', 70000)
        ->assertJsonPath('data.meta.summary.tax', 0)
        ->assertJsonPath('data.meta.summary.shipping_cost', 0)
        ->assertJsonPath('data.meta.summary.discount', 0)
        ->assertJsonPath('data.meta.summary.total', 70000)
        ->assertJsonPath('data.meta.items.0.product_id', $product->id)
        ->assertJsonPath('data.meta.items.0.requested_quantity', 2);
});

it('supports add, update, remove, apply coupon, and remove coupon', function () {
    $user = User::factory()->create([
        'type' => UserTypeCast::MEMBER->value,
    ]);

    $category = Category::create([
        'name' => 'Flow Category',
        'url' => 'flow-category',
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

    $this->actingAs($user)
        ->postJson('/api/cart', [
            'product_slug' => $product->url,
            'quantity' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->actingAs($user)
        ->putJson('/api/cart/'.$product->url, [
            'quantity' => 3,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $voucher = VoucherManager::create([
        'name' => 'Flow Voucher',
        'description' => 'Cart voucher',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(7)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 5,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 90000],
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

    $this->actingAs($user)
        ->postJson('/api/cart/coupon', [
            'code' => $code,
            'shipping_address_id' => $shippingAddress->uuid,
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.summary.discount', 10500);

    $this->actingAs($user)
        ->deleteJson('/api/cart/coupon', [
            'shipping_address_id' => $shippingAddress->uuid,
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.summary.discount', 0);

    $this->actingAs($user)
        ->deleteJson('/api/cart/'.$product->url)
        ->assertOk()
        ->assertJsonPath('success', true);
});
