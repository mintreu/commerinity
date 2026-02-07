<?php

declare(strict_types=1);

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ProductStock Model', function () {
    it('creates stock with purchase entry fields', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'sold_quantity' => 0,
            'landing_cost' => 30000, // ₹300
        ]);

        expect($stock)->toBeInstanceOf(ProductStock::class)
            ->and($stock->landing_cost)->toBe(30000);
    });

    it('calculates available stock correctly', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'sold_quantity' => 25,
        ])->fresh();

        expect($stock->available_stock)->toBe(75)
            ->and($stock->in_stock)->toBeTrue()
            ->and($stock->in_stock_quantity)->toBe(75);
    });

    it('consumes stock correctly', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'sold_quantity' => 0,
        ]);

        $result = $stock->consumeStock(30);

        expect($result)->toBeTrue()
            ->and($stock->fresh()->sold_quantity)->toBe(30)
            ->and($stock->fresh()->available_stock)->toBe(70);
    });

    it('prevents consuming more than available stock', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 10,
            'sold_quantity' => 5,
        ]);

        $result = $stock->consumeStock(10); // Only 5 available

        expect($result)->toBeFalse()
            ->and($stock->fresh()->sold_quantity)->toBe(5);
    });
});

describe('ProductStock Scopes', function () {
    it('returns in-stock entries in FIFO order', function () {
        $product = Product::factory()->create();

        $first = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
        ]);
        $first->update(['created_at' => now()->subDay()]);

        $second = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
        ]);

        $stocks = ProductStock::query()->where('in_stock', true)->get();

        expect($stocks)->toHaveCount(2);
        expect($stocks->first()->id)->toBeLessThan($stocks->last()->id);
    });
});

describe('ProductStock Factory', function () {
    it('creates stock with default fields', function () {
        $stock = ProductStock::factory()->create();

        expect($stock->init_quantity)->toBeGreaterThan(0)
            ->and($stock->landing_cost)->toBeGreaterThan(0);
    });

    it('creates low stock entry', function () {
        $stock = ProductStock::factory()->lowStock()->create();

        expect($stock->isLowStock())->toBeTrue();
    });

    it('creates out of stock entry', function () {
        $stock = ProductStock::factory()->outOfStock()->create()->fresh();

        expect($stock->in_stock)->toBeFalse()
            ->and($stock->available_stock)->toBe(0);
    });
});
