<?php

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Services\Ecommerce\PriceCalculationService;
use App\Services\MoneyService;

beforeEach(function () {
    $this->priceService = new PriceCalculationService(new MoneyService());
});

// Test 1: Basic price calculation from landing cost
it('calculates price from landing cost and profit margin', function () {
    $landingCost = 5000; // 50.00 ₹ in paise
    $profitMargin = 20.0; // 20%

    // Expected: 5000 * 1.2 = 6000 paise (60.00 ₹)
    $price = $this->priceService->calculateFromCost($landingCost, $profitMargin);

    expect($price)->toBe(6000);
});

// Test 2: Stock price calculation (with override)
it('uses override price when set on stock', function () {
    $stock = new ProductStock([
        'landing_cost' => 5000,
        'profit_margin' => 20.0,
        'price' => 5500, // Override price
    ]);

    $price = $this->priceService->getStockPrice($stock);

    // Should use override price (5500) not calculated price (6000)
    expect($price)->toBe(5500);
});

// Test 3: Stock price calculation (without override)
it('calculates price from cost when no override', function () {
    $stock = new ProductStock([
        'landing_cost' => 5000,
        'profit_margin' => 20.0,
        'price' => null, // No override
    ]);

    $price = $this->priceService->getStockPrice($stock);

    // Should calculate: 5000 * 1.2 = 6000
    expect($price)->toBe(6000);
});

// Test 4: FIFO pricing with multiple stocks
it('gets price from first available stock (FIFO)', function () {
    $stocks = collect([
        new ProductStock(['landing_cost' => 5000, 'profit_margin' => 20.0, 'price' => null, 'in_stock' => false]),
        new ProductStock(['landing_cost' => 6000, 'profit_margin' => 25.0, 'price' => null, 'in_stock' => true]),
        new ProductStock(['landing_cost' => 4000, 'profit_margin' => 30.0, 'price' => null, 'in_stock' => true]),
    ]);

    $price = $this->priceService->getProductPriceFromAvailableStock($stocks);

    // Should get from first in-stock stock (index 1): 6000 * 1.25 = 7500
    expect($price)->toBe(7500);
});

// Test 5: No available stock returns 0
it('returns 0 when no stock available', function () {
    $stocks = collect([
        new ProductStock(['in_stock' => false]),
        new ProductStock(['in_stock' => false]),
    ]);

    $price = $this->priceService->getProductPriceFromAvailableStock($stocks);

    expect($price)->toBe(0);
});

// Test 6: Sale price calculation (percentage off)
it('calculates percentage off sale price', function () {
    $originalPrice = 10000; // 100.00 ₹
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'percentage_off', 20.0);

    // 10000 - 20% = 8000 paise
    expect($salePrice)->toBe(8000);
});

// Test 7: Sale price calculation (fixed amount off)
it('calculates fixed amount off sale price', function () {
    $originalPrice = 10000; // 100.00 ₹
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'fixed_amount_off', 25.0);

    // 10000 - 2500 = 7500 paise (25.00 ₹)
    expect($salePrice)->toBe(7500);
});

// Test 8: Sale price calculation (fixed price)
it('calculates fixed price sale', function () {
    $originalPrice = 10000; // 100.00 ₹
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'fixed_price', 75.0);

    // Fixed price: 75.00 ₹ = 7500 paise
    expect($salePrice)->toBe(7500);
});

// Test 9: Cheapest stock selection
it('finds cheapest available stock', function () {
    $stocks = collect([
        new ProductStock(['landing_cost' => 5000, 'profit_margin' => 50.0, 'price' => null, 'in_stock' => true]),
        new ProductStock(['landing_cost' => 4000, 'profit_margin' => 50.0, 'price' => null, 'in_stock' => true]), // Cheaper
        new ProductStock(['landing_cost' => 6000, 'profit_margin' => 50.0, 'price' => null, 'in_stock' => true]),
    ]);

    $cheapest = $this->priceService->getCheapestAvailableStock($stocks);

    expect($cheapest)->toBe($stocks[1]);
    expect($this->priceService->getStockPrice($cheapest))->toBe(6000); // 4000 * 1.5 = 6000
});

// Test 10: Price range calculation
it('calculates correct price range', function () {
    $stocks = collect([
        new ProductStock(['landing_cost' => 5000, 'profit_margin' => 20.0, 'price' => null, 'in_stock' => true]), // 6000
        new ProductStock(['landing_cost' => 7000, 'profit_margin' => 30.0, 'price' => null, 'in_stock' => true]), // 9100
        new ProductStock(['landing_cost' => 6000, 'profit_margin' => 25.0, 'price' => null, 'in_stock' => true]), // 7500
    ]);

    $min = $this->priceService->getMinimumPrice($stocks);
    $max = $this->priceService->getMaximumPrice($stocks);

    expect($min)->toBe(6000);
    expect($max)->toBe(9100);
});

// Test 11: Discount percentage calculation
it('calculates correct discount percentage', function () {
    $originalPrice = 10000;
    $salePrice = 8000;

    $discount = $this->priceService->calculateDiscountPercent($originalPrice, $salePrice);

    expect($discount)->toBe(20.0); // 20% discount
});

// Test 12: ProductResource integration test (simulated)
it('ensures ProductResource uses new pricing logic', function () {
    // Create a product with stock
    $product = Product::factory()->create();
    $stock = ProductStock::factory()->create([
        'product_id' => $product->id,
        'landing_cost' => 5000,
        'profit_margin' => 20.0,
        'price' => null,
        'in_stock' => true,
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    // Load relationships
    $product->load(['availableStocks']);

    $resource = new \App\Http\Resources\Ecommerce\ProductResource($product);
    $data = $resource->toArray(request());

    // Price should be calculated from stock (5000 * 1.2 = 6000)
    expect($data['price'])->toBe(6000);
    expect($data['price_formatted'])->toContain('60.00');
});