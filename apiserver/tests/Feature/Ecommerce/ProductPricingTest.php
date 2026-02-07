<?php

declare(strict_types=1);

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('always returns the canonical product price regardless of stock details', function () {
    $product = Product::factory()->create([
        'price' => 5000, // ₹50
        'bv' => 100,
        'pv' => 80,
        'reward_points' => 50,
    ]);

    ProductStock::factory()->for($product)->create([
        'landing_cost' => 10000,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    $freshProduct = Product::findOrFail($product->id);

    expect($freshProduct->getPrice())->toBe(5000)
        ->and($freshProduct->getPrice('110001'))->toBe(5000)
        ->and($freshProduct->getDisplayPrice())->toBe(5000);
});
