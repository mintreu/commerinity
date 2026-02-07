<?php

use App\Casts\SaleActionTypeCast;
use App\Casts\VoucherActionTypeCast;
use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\User;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\Ecommerce\VoucherManager;
use Database\Factories\AddressFactory;
use Database\Factories\Ecommerce\ProductStockFactory;

function makeCategory(string $name): Category
{
    return Category::create([
        'name' => $name,
        'url' => str($name)->slug()->value(),
        'status' => true,
    ]);
}

function makeProductWithStock(int $price, Category $category, ?User $user = null): array
{
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => $price,
    ]);

    $stockAddress = AddressFactory::new()->warehouse()->create();

    ProductStockFactory::new()->create([
        'product_id' => $product->id,
        'address_id' => $stockAddress->id,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    $shippingAddress = $user
        ? AddressFactory::new()->forUser($user)->create()
        : null;

    return [$product, $shippingAddress];
}

it('prioritizes end_other_rules sale over sorted sales', function () {
    $user = User::factory()->create();

    $category = makeCategory('Sale Priority');
    [$product, $shippingAddress] = makeProductWithStock(10000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $saleA = Sale::factory()->create([
        'name' => 'Lower Priority',
        'conditions' => [
            ['attribute' => 'product|price', 'operator' => '>', 'value' => 0],
        ],
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 1000,
        'sort_order' => 0,
        'end_other_rules' => false,
    ]);

    $saleB = Sale::factory()->create([
        'name' => 'End Other Rules',
        'conditions' => [
            ['attribute' => 'product|price', 'operator' => '>', 'value' => 0],
        ],
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 3000,
        'sort_order' => 10,
        'end_other_rules' => true,
    ]);

    SaleProduct::factory()->create([
        'sale_id' => $saleA->id,
        'product_id' => $product->id,
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 1000,
        'sale_price' => 0,
        'sort_order' => 0,
        'end_other_rules' => false,
    ]);

    SaleProduct::factory()->create([
        'sale_id' => $saleB->id,
        'product_id' => $product->id,
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 3000,
        'sale_price' => 0,
        'sort_order' => 10,
        'end_other_rules' => true,
    ]);

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['items'][0]['unit_price'])->toBe(7000);
    expect($meta['items'][0]['summary']['discount'])->toBe(3000);
});

it('ignores sale when conditions do not match', function () {
    $user = User::factory()->create();

    $category = makeCategory('Sale Conditions');
    [$product, $shippingAddress] = makeProductWithStock(10000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $sale = Sale::factory()->create([
        'name' => 'Condition Fails',
        'conditions' => [
            ['attribute' => 'product|price', 'operator' => '>', 'value' => 20000],
        ],
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 4000,
    ]);

    SaleProduct::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 4000,
        'sale_price' => 5000,
    ]);

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['items'][0]['unit_price'])->toBe(10000);
    expect($meta['items'][0]['summary']['discount'])->toBe(0);
});

it('uses sale_product sale_price when available', function () {
    $user = User::factory()->create();

    $category = makeCategory('Sale Price');
    [$product, $shippingAddress] = makeProductWithStock(12000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 2,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $sale = Sale::factory()->create([
        'name' => 'Explicit Price',
        'conditions' => [],
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
        'discount_amount' => 0,
    ]);

    SaleProduct::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'sale_price' => 6500,
        'action_type' => SaleActionTypeCast::BY_FIXED->value,
    ]);

    $meta = (new CartService($user))->getMeta($shippingAddress, true);

    expect($meta['items'][0]['unit_price'])->toBe(6500);
    expect($meta['items'][0]['summary']['discount'])->toBe(11000);
    expect($meta['summary']['sub_total'])->toBe(13000);
});

it('applies cart-level voucher when cart conditions pass', function () {
    $user = User::factory()->create();

    $category = makeCategory('Cart Voucher');
    [$product, $shippingAddress] = makeProductWithStock(15000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 2,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $voucher = VoucherManager::create([
        'name' => 'Cart Percent Voucher',
        'description' => '10% off cart',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(5)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 0,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 20000],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::CART_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 0,
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

    expect($meta['summary']['sub_total'])->toBe(30000);
    expect($meta['summary']['discount'])->toBe(3000);
    expect($meta['summary']['total'])->toBe(27000);
});

it('rejects voucher when cart conditions fail', function () {
    $user = User::factory()->create();

    $category = makeCategory('Cart Voucher Fail');
    [$product, $shippingAddress] = makeProductWithStock(15000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 2,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $voucher = VoucherManager::create([
        'name' => 'Cart Percent Voucher Fail',
        'description' => '10% off cart',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(5)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 0,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 50000],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::CART_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 0,
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

    expect($meta['summary']['discount'])->toBe(0);
    expect($meta['summary']['total'])->toBe(30000);
});

it('applies product-level voucher when product condition matches', function () {
    $user = User::factory()->create();

    $category = makeCategory('Product Voucher');
    [$product, $shippingAddress] = makeProductWithStock(18000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $voucher = VoucherManager::create([
        'name' => 'Product Fixed Voucher',
        'description' => 'Fixed item discount',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(5)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 0,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::BY_FIXED,
        'discount_amount' => 2000,
        'discount_quantity' => 0,
        'discount_step' => null,
        'apply_to_shipping' => false,
        'free_shipping' => false,
        'min_cart_value' => 0,
        'min_quantity' => 0,
        'sort_order' => 1,
    ]);

    $voucher->categories()->sync([$category->id]);

    $code = $voucher->primaryCode?->code ?? $voucher->codes()->first()?->code;

    $cart = new CartService($user);
    $cart->setCouponCode($code);

    $meta = $cart->getMeta($shippingAddress, true);

    expect($meta['summary']['sub_total'])->toBe(18000);
    expect($meta['summary']['discount'])->toBe(2000);
    expect($meta['summary']['total'])->toBe(16000);
});

it('applies voucher when match_any condition passes', function () {
    $user = User::factory()->create();

    $category = makeCategory('Voucher Match Any');
    [$product, $shippingAddress] = makeProductWithStock(12000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $voucher = VoucherManager::create([
        'name' => 'Match Any Voucher',
        'description' => 'Matches any condition',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(5)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 0,
        'times_used' => 0,
        'condition_type' => 'match_any',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 50000],
            ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 0,
        'discount_step' => null,
        'apply_to_shipping' => false,
        'free_shipping' => false,
        'min_cart_value' => 0,
        'min_quantity' => 0,
        'sort_order' => 1,
    ]);

    $voucher->categories()->sync([$category->id]);

    $code = $voucher->primaryCode?->code ?? $voucher->codes()->first()?->code;

    $cart = new CartService($user);
    $cart->setCouponCode($code);

    $meta = $cart->getMeta($shippingAddress, true);

    expect($meta['summary']['discount'])->toBe(1200);
    expect($meta['summary']['total'])->toBe(10800);
});

it('rejects match_any voucher when no condition matches', function () {
    $user = User::factory()->create();

    $category = makeCategory('Voucher Match Any Fail');
    [$product, $shippingAddress] = makeProductWithStock(12000, $category, $user);

    Cart::create([
        'cartable_id' => $product->id,
        'cartable_type' => Product::class,
        'quantity' => 1,
        'ownerable_type' => User::class,
        'ownerable_id' => $user->id,
        'is_guest' => false,
    ]);

    $voucher = VoucherManager::create([
        'name' => 'Match Any Fail Voucher',
        'description' => 'Matches any condition',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(5)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 0,
        'times_used' => 0,
        'condition_type' => 'match_any',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 50000],
            ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [999999]],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 0,
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

    expect($meta['summary']['discount'])->toBe(0);
    expect($meta['summary']['total'])->toBe(12000);
});
