<?php

use App\Casts\SaleActionTypeCast;
use App\Casts\VoucherActionTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Ecommerce\Voucher;
use App\Models\Ecommerce\VoucherCode;
use App\Services\Ecommerce\SaleManager;
use App\Services\Ecommerce\VoucherManager;

it('reindexes sitewide and category sales into sale_products', function () {
    $category = Category::create([
        'name' => 'Demo Category',
        'url' => 'demo-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
    ]);

    $siteSale = Sale::create([
        'name' => 'Sitewide Demo Sale',
        'description' => 'All products',
        'starts_from' => now()->subDay(),
        'ends_till' => now()->addDays(5),
        'status' => true,
        'condition_type' => 'match_any',
        'conditions' => [],
        'end_other_rules' => false,
        'action_type' => SaleActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'sort_order' => 1,
    ]);

    $categorySale = Sale::create([
        'name' => 'Category Demo Sale',
        'description' => 'Category only',
        'starts_from' => now()->subDay(),
        'ends_till' => now()->addDays(5),
        'status' => true,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
        ],
        'end_other_rules' => false,
        'action_type' => SaleActionTypeCast::BY_FIXED,
        'discount_amount' => 500,
        'sort_order' => 2,
    ]);
    $categorySale->categories()->sync([$category->id]);

    SaleManager::make()->reindexSaleableProducts();

    expect(SaleProduct::where('sale_id', $siteSale->id)->where('product_id', $product->id)->exists())->toBeTrue();
    expect(SaleProduct::where('sale_id', $categorySale->id)->where('product_id', $product->id)->exists())->toBeTrue();
});

it('creates sale with target user types', function () {
    $category = Category::create([
        'name' => 'Target Category',
        'url' => 'target-category',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 12000,
    ]);

    $sale = Sale::create([
        'name' => 'Member Sale',
        'description' => 'Members only',
        'starts_from' => now()->subDay(),
        'ends_till' => now()->addDays(5),
        'status' => true,
        'condition_type' => 'match_any',
        'conditions' => [],
        'end_other_rules' => false,
        'action_type' => SaleActionTypeCast::BY_PERCENT,
        'discount_amount' => 5,
        'sort_order' => 1,
        'target_user_types' => [\App\Casts\UserTypeCast::MEMBER->value],
    ]);
    $sale->products()->sync([$product->id]);

    SaleManager::make()->reindexSaleableProducts();

    expect(SaleProduct::where('sale_id', $sale->id)->where('product_id', $product->id)->exists())->toBeTrue();
});

it('updates sale_products when sale changes and reindex runs', function () {
    $category = Category::create([
        'name' => 'Demo Category 2',
        'url' => 'demo-category-2',
        'status' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 20000,
    ]);

    $sale = Sale::create([
        'name' => 'Update Demo Sale',
        'starts_from' => now()->subDay(),
        'ends_till' => now()->addDays(5),
        'status' => true,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
        ],
        'end_other_rules' => false,
        'action_type' => SaleActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'sort_order' => 1,
    ]);
    $sale->categories()->sync([$category->id]);

    SaleManager::make()->reindexSaleableProducts();
    $first = SaleProduct::where('sale_id', $sale->id)->first();
    expect($first)->not->toBeNull();
    $firstDiscount = $first->discount_amount;

    $sale->update(['discount_amount' => 15]);
    SaleManager::make()->reindexSingleSale($sale);
    $updated = SaleProduct::where('sale_id', $sale->id)->first();

    expect($updated->discount_amount)->toBe(15);
    expect($updated->discount_amount)->not->toBe($firstDiscount);
});

it('creates voucher with codes via manager', function () {
    $voucher = VoucherManager::create([
        'name' => 'Cart Demo Voucher',
        'description' => 'Cart voucher',
        'starts_from' => now()->subDay()->toDateString(),
        'ends_till' => now()->addDays(7)->toDateString(),
        'status' => true,
        'usage_per_customer' => 1,
        'coupon_usage_limit' => 5,
        'times_used' => 0,
        'condition_type' => 'match_all',
        'conditions' => [
            ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 10000],
        ],
        'end_other_rules' => false,
        'action_type' => VoucherActionTypeCast::BY_PERCENT,
        'discount_amount' => 10,
        'discount_quantity' => 1,
        'discount_step' => null,
        'apply_to_shipping' => false,
        'free_shipping' => false,
        'min_cart_value' => 0,
        'min_quantity' => 0,
        'sort_order' => 1,
    ]);

    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->codes()->count())->toBe(5);

    $code = VoucherCode::where('voucher_id', $voucher->id)->first();
    expect($code)->not->toBeNull();
    expect($code->usage_per_user)->toBe(1);
});
