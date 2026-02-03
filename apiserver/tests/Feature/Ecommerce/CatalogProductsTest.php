<?php

declare(strict_types=1);

use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns consistent prices across catalog endpoints', function () {
    $category = Category::create(['name' => 'Catalog', 'url' => 'catalog', 'status' => true]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 12345,
    ]);

    $listResponse = $this->getJson('/api/catalog/products?per_page=1');
    $listResponse->assertSuccessful();
    expect($listResponse->json('data.0.price'))->toBe(12345);

    $detailResponse = $this->getJson("/api/catalog/products/{$product->url}");
    $detailResponse->assertSuccessful();
    expect($detailResponse->json('data.price'))->toBe(12345);

    $featuredResponse = $this->getJson('/api/catalog/featured');
    $featuredResponse->assertSuccessful();
    expect($featuredResponse->json('data.best_sellers.0.price'))->toBe(12345);
    expect($featuredResponse->json('data.new_arrivals.0.price'))->toBe(12345);
});

it('sorts and paginates catalog products using product price', function () {
    $category = Category::create(['name' => 'Sorting', 'url' => 'sorting', 'status' => true]);

    $cheap = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
    ]);
    $mid = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 20000,
    ]);
    $expensive = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 30000,
    ]);

    ProductStock::factory()->for($cheap)->create(['price' => 10000]);
    ProductStock::factory()->for($mid)->create(['price' => 20000]);
    ProductStock::factory()->for($expensive)->create(['price' => 30000]);

    $ascending = $this->getJson('/api/catalog/products?sort=price_asc&per_page=3');
    $ascending->assertSuccessful();
    expect($ascending->json('data.0.price'))->toBe(10000)
        ->and($ascending->json('data.1.price'))->toBe(20000)
        ->and($ascending->json('data.2.price'))->toBe(30000);

    $descending = $this->getJson('/api/catalog/products?sort=price_desc&per_page=3');
    $descending->assertSuccessful();
    expect($descending->json('data.0.price'))->toBe(30000)
        ->and($descending->json('data.1.price'))->toBe(20000)
        ->and($descending->json('data.2.price'))->toBe(10000);
});

it('filters catalog products by price range using product price', function () {
    $category = Category::create(['name' => 'Price Filter', 'url' => 'price-filter', 'status' => true]);

    Product::factory()->create([
        'category_id' => $category->id,
        'price' => 10000,
    ]);
    Product::factory()->create([
        'category_id' => $category->id,
        'price' => 25000,
    ]);
    Product::factory()->create([
        'category_id' => $category->id,
        'price' => 50000,
    ]);

    $response = $this->getJson('/api/catalog/products?min_price=150&max_price=400&per_page=10');
    $response->assertSuccessful();

    $prices = collect($response->json('data'))->pluck('price')->values();
    expect($prices->all())->toEqual([25000]);
});
