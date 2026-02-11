<?php

declare(strict_types=1);

use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a product', function () {
    $filterGroup = FilterGroup::create(['name' => 'Electronics', 'type' => 'product']);

    $product = Product::create([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'url' => 'test-product',
        'type' => ProductTypeCast::SIMPLE->value,
        'status' => 'Draft',
        'filter_group_id' => $filterGroup->id,
        'price' => 100000, // ₹1000 in paise
        'view_count' => 0,
    ]);

    expect($product->id)->toBeGreaterThan(0)
        ->and($product->name)->toBe('Test Product')
        ->and($product->price)->toBe(100000);
});

it('can create a bundle product', function () {
    $filterGroup = FilterGroup::create(['name' => 'Bundles', 'type' => 'product']);

    $bundle = Product::create([
        'name' => 'Bundle Pack',
        'sku' => 'BUNDLE-001',
        'url' => 'bundle-pack',
        'type' => ProductTypeCast::BUNDLE->value,
        'status' => 'Draft',
        'filter_group_id' => $filterGroup->id,
        'price' => 150000,
        'view_count' => 0,
    ]);

    expect($bundle->type->value)->toBe(ProductTypeCast::BUNDLE->value)
        ->and($bundle->sku)->toBe('BUNDLE-001');
});

it('can create product with stock', function () {
    $filterGroup = FilterGroup::create(['name' => 'Electronics', 'type' => 'product']);

    $product = Product::create([
        'name' => 'Product with Stock',
        'sku' => 'TEST-002',
        'url' => 'product-with-stock',
        'type' => ProductTypeCast::SIMPLE->value,
        'status' => 'Published',
        'filter_group_id' => $filterGroup->id,
        'price' => 50000,
    ]);

    $stock = ProductStock::create([
        'product_id' => $product->id,
        'init_quantity' => 100,
        'sold_quantity' => 0,
        'priority' => 1,
        'low_stock_threshold' => 10,
        'notify_on_low_stock' => true,
    ]);

    $stock->refresh(); // Refresh to get generated column

    expect($stock->in_stock_quantity)->toBe(100)
        ->and($stock->in_stock)->toBeTrue()
        ->and($stock->inStock())->toBeTrue();
});

it('can create hierarchical categories', function () {
    $parent = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'url' => 'electronics',
        'status' => true,
    ]);

    $child = Category::create([
        'name' => 'Mobile Phones',
        'slug' => 'mobile-phones',
        'url' => 'electronics/mobile-phones',
        'parent_id' => $parent->id,
        'status' => true,
    ]);

    expect($child->parent_id)->toBe($parent->id)
        ->and($parent->children()->count())->toBe(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

it('stock in_stock_quantity is calculated correctly', function () {
    $filterGroup = FilterGroup::create(['name' => 'Electronics', 'type' => 'product']);

    $product = Product::create([
        'name' => 'Stock Test',
        'sku' => 'TEST-003',
        'url' => 'stock-test',
        'type' => ProductTypeCast::SIMPLE->value,
        'status' => 'Published',
        'filter_group_id' => $filterGroup->id,
        'price' => 50000,
    ]);

    $stock = ProductStock::create([
        'product_id' => $product->id,
        'init_quantity' => 50,
        'sold_quantity' => 10,
        'priority' => 1,
    ]);

    // Refresh to get generated column value
    $stock->refresh();

    expect($stock->in_stock_quantity)->toBe(40)
        ->and($stock->in_stock)->toBeTrue();
});
