<?php

use App\Casts\GstTaxCast;
use App\Casts\KycStatusCast;
use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Kyc;
use App\Models\User;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\Geo\Block;
use App\Services\Ecommerce\CartService\CartService;
use Database\Factories\Ecommerce\ProductStockFactory;

function makeIndia(): Country
{
    return Country::factory()->india()->create();
}

function makeState(string $name, string $code, Country $country): State
{
    return State::factory()->create([
        'name' => $name,
        'code' => $code,
        'country_id' => $country->id,
    ]);
}

function makeBlock(State $state): Block
{
    return Block::factory()->forState($state)->create([
        'state_code' => $state->code,
    ]);
}

function makeAddressForState(?User $user, State $state, Block $block, bool $warehouse = false): Address
{
    return Address::factory()->create([
        'addressable_type' => $user ? User::class : null,
        'addressable_id' => $user?->id,
        'block_id' => $block->id,
        'state_code' => $state->code,
        'country_code' => $state->country->iso_code_2,
        'pickup_location' => $warehouse,
        'type' => $warehouse ? \App\Casts\AddressTypeCast::HUB : \App\Casts\AddressTypeCast::HOME,
    ]);
}

it('calculates CGST/SGST when shipping and warehouse are same state', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $country = makeIndia();
    $wb = makeState('West Bengal', 'WB', $country);
    $wbBlock = makeBlock($wb);

    $user = User::factory()->create();
    $shippingAddress = makeAddressForState($user, $wb, $wbBlock);
    $stockAddress = makeAddressForState(null, $wb, $wbBlock, true);

    $category = Category::create([
        'name' => 'GST Category',
        'url' => 'gst-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
        'gst_tax_type' => GstTaxCast::GST_5->value,
    ]);

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

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['summary']['tax'])->toBe(1000);
    expect($meta['tax_breakdown'][0]['gst_type'])->toBe('CGST/SGST');
});

it('calculates IGST when shipping and warehouse are different states', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $country = makeIndia();
    $wb = makeState('West Bengal', 'WB', $country);
    $ka = makeState('Karnataka', 'KA', $country);
    $wbBlock = makeBlock($wb);
    $kaBlock = makeBlock($ka);

    $user = User::factory()->create();
    $shippingAddress = makeAddressForState($user, $wb, $wbBlock);
    $stockAddress = makeAddressForState(null, $ka, $kaBlock, true);

    $category = Category::create([
        'name' => 'GST Category 2',
        'url' => 'gst-category-2',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
        'gst_tax_type' => GstTaxCast::GST_5->value,
    ]);

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

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['summary']['tax'])->toBe(1000);
    expect($meta['tax_breakdown'][0]['gst_type'])->toBe('IGST');
});

it('uses category tax slab when product GST is null', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $country = makeIndia();
    $wb = makeState('West Bengal', 'WB', $country);
    $wbBlock = makeBlock($wb);

    $user = User::factory()->create();
    $shippingAddress = makeAddressForState($user, $wb, $wbBlock);
    $stockAddress = makeAddressForState(null, $wb, $wbBlock, true);

    $category = Category::create([
        'name' => 'GST Category 3',
        'url' => 'gst-category-3',
        'status' => true,
        'tax_slab' => GstTaxCast::GST_18->value,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
        'gst_tax_type' => null,
    ]);

    ProductStockFactory::new()->create([
        'product_id' => $product->id,
        'address_id' => $stockAddress->id,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['summary']['tax'])->toBe(1800);
});

it('calculates native shipping cost based on weight slabs', function () {
    config()->set('shipping.native.base_rate_paise', 5000);
    config()->set('shipping.native.base_weight_grams', 1000);
    config()->set('shipping.native.rate_per_kg_paise', 2000);
    config()->set('shipping.native.default_item_weight_grams', 500);

    $country = makeIndia();
    $wb = makeState('West Bengal', 'WB', $country);
    $wbBlock = makeBlock($wb);

    $user = User::factory()->create();
    $shippingAddress = makeAddressForState($user, $wb, $wbBlock);
    $stockAddress = makeAddressForState(null, $wb, $wbBlock, true);

    $category = Category::create([
        'name' => 'Shipping Category',
        'url' => 'shipping-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
        'weight_grams' => 600,
    ]);

    ProductStockFactory::new()->create([
        'product_id' => $product->id,
        'address_id' => $stockAddress->id,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 3,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['summary']['shipping_cost'])->toBe(7000);
});

it('includes customer GST number when KYC is approved', function () {
    config()->set('shipping.native.base_rate_paise', 0);
    config()->set('shipping.native.base_weight_grams', 0);
    config()->set('shipping.native.rate_per_kg_paise', 0);
    config()->set('shipping.native.default_item_weight_grams', 0);

    $user = User::factory()->create();
    $user->kyc()->create([
        'kyc_type' => \App\Casts\KycTypeCast::PERSONAL->value,
        'pan_number' => 'ABCDE1234F',
        'gst_number' => '29ABCDE1234F1Z5',
        'status' => KycStatusCast::APPROVED->value,
        'submitted_at' => now(),
        'reviewed_at' => now(),
        'reviewed_by' => $user->id,
    ]);

    $meta = (new CartService($user))->getMeta();

    expect($meta['customer']['profile']['gst_number'])->toBe('29ABCDE1234F1Z5');
});
