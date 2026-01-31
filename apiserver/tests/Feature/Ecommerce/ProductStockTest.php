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
            'profit_margin' => 66.67,
            'price' => 50000,
            'bv' => 2000, // 20 BV points
            'pv' => 1600, // 16 PV points
            'reward_points' => 200, // 200 reward points
            'is_commissionable' => true,
        ]);

        expect($stock)->toBeInstanceOf(ProductStock::class)
            ->and($stock->landing_cost)->toBe(30000)
            ->and($stock->bv)->toBe(2000)
            ->and($stock->pv)->toBe(1600)
            ->and($stock->reward_points)->toBe(200)
            ->and($stock->is_commissionable)->toBeTrue();
    });

    it('calculates available stock correctly', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'sold_quantity' => 25,
        ]);

        // Refresh to get computed columns
        $stock = $stock->fresh();

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

    it('calculates profit per unit correctly', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'landing_cost' => 30000, // ₹300
            'price' => 50000, // ₹500
        ]);

        expect($stock->getProfitPerUnit())->toBe(20000); // ₹200 profit
    });

    it('calculates stock price from landing cost and profit margin when override price is null', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'landing_cost' => 30000,
            'profit_margin' => 50.0,
            'price' => null,
        ]);

        expect($stock->getEffectivePrice())->toBe(45000);
    });

    it('calculates BV from profit correctly', function () {
        // 10% BV rate
        expect(ProductStock::calculateBvFromProfit(20000, 10.0))->toBe(2000);
        expect(ProductStock::calculateBvFromProfit(15000, 10.0))->toBe(1500);
    });

    it('calculates reward points from profit correctly', function () {
        // 1 point per rupee
        expect(ProductStock::calculateRewardPoints(20000))->toBe(200); // ₹200 = 200 points
        expect(ProductStock::calculateRewardPoints(15050))->toBe(150); // ₹150.50 = 150 points (floor)
    });

    it('checks if stock can generate commission', function () {
        $product = Product::factory()->create();

        $commissionable = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'bv' => 1000,
            'is_commissionable' => true,
        ]);

        $nonCommissionable = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'bv' => 0,
            'is_commissionable' => true,
        ]);

        expect($commissionable->canGenerateCommission())->toBeTrue()
            ->and($nonCommissionable->canGenerateCommission())->toBeFalse();
    });

    it('checks quantity range for wholesale', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 1000,
            'min_quantity' => 10,
            'max_quantity' => 100,
        ]);

        expect($stock->isInRange(5))->toBeFalse()   // Below min
            ->and($stock->isInRange(10))->toBeTrue()  // At min
            ->and($stock->isInRange(50))->toBeTrue()  // In range
            ->and($stock->isInRange(100))->toBeTrue() // At max
            ->and($stock->isInRange(101))->toBeFalse(); // Above max
    });

    it('handles unlimited max quantity', function () {
        $product = Product::factory()->create();

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 1000,
            'min_quantity' => 1,
            'max_quantity' => null,
        ]);

        expect($stock->isInRange(1))->toBeTrue()
            ->and($stock->isInRange(500))->toBeTrue()
            ->and($stock->isInRange(1000))->toBeTrue();
    });
});

describe('ProductStock Scopes', function () {
    it('filters commissionable stock', function () {
        $product = Product::factory()->create();

        ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'bv' => 1000,
            'is_commissionable' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'bv' => 0,
            'is_commissionable' => false,
        ]);

        expect(ProductStock::commissionable()->count())->toBe(1);
    });

    it('filters stock by quantity range (FIFO)', function () {
        $product = Product::factory()->create();

        // First stock entry (should be returned first - FIFO)
        $first = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'min_quantity' => 1,
            'max_quantity' => 50,
        ]);
        // Manually set created_at to older date
        $first->update(['created_at' => now()->subDay()]);

        // Second stock entry
        $second = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => 100,
            'min_quantity' => 1,
            'max_quantity' => null,
        ]);

        $stocks = ProductStock::forQuantity(30)->get();

        expect($stocks)->toHaveCount(2);
        // FIFO - oldest first (compare by ID since created_at might be same in fast execution)
        expect($stocks->first()->id)->toBeLessThan($stocks->last()->id);
    });
});

describe('ProductStock Factory', function () {
    it('creates stock with calculated Affiliate values', function () {
        $stock = ProductStock::factory()->create();

        expect($stock->bv)->toBeGreaterThan(0)
            ->and($stock->pv)->toBeGreaterThan(0)
            ->and($stock->reward_points)->toBeGreaterThanOrEqual(0)
            ->and($stock->is_commissionable)->toBeTrue();
    });

    it('creates non-commissionable stock', function () {
        $stock = ProductStock::factory()->nonCommissionable()->create();

        expect($stock->is_commissionable)->toBeFalse()
            ->and($stock->bv)->toBe(0)
            ->and($stock->pv)->toBe(0);
    });

    it('creates wholesale stock', function () {
        $stock = ProductStock::factory()->wholesale(10, 100)->create();

        expect($stock->min_quantity)->toBe(10)
            ->and($stock->max_quantity)->toBe(100)
            ->and($stock->wholesale_unit_quantity)->toBe(10);
    });

    it('creates low stock entry', function () {
        $stock = ProductStock::factory()->lowStock()->create();

        expect($stock->isLowStock())->toBeTrue();
    });

    it('creates out of stock entry', function () {
        $stock = ProductStock::factory()->outOfStock()->create();

        // Refresh to get computed columns
        $stock = $stock->fresh();

        expect($stock->in_stock)->toBeFalse()
            ->and($stock->available_stock)->toBe(0);
    });
});
