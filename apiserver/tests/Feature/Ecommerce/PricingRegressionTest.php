<?php

declare(strict_types=1);

namespace Tests\Feature\Ecommerce;

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product price remains canonical even if stocks imply a different value', function () {
    $product = Product::factory()->create([
        'price' => 5000,
    ]);

    ProductStock::factory()->for($product)->create([
        'landing_cost' => 10000,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    expect($product->getPrice())->toBe(5000);
});
