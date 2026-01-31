<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Geo\Block;
use App\Models\Geo\State;
use App\Services\Ecommerce\PriceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeWarehouse(string $stateCode, string $postalCode): Address
{
    $state = State::factory()->create(['code' => $stateCode]);
    $block = Block::factory()->forState($state)->create();

    return Address::factory()
        ->warehouse()
        ->forBlock($block)
        ->create([
            'postal_code' => $postalCode,
        ]);
}

it('prioritizes exact postal code matches before falling back to FIFO', function () {
    $product = Product::factory()->create();
    $service = app(PriceCalculationService::class);

    $warehouseA = makeWarehouse('WB', '111111');
    $warehouseB = makeWarehouse('WB', '222222');

    $first = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouseA->id,
        'landing_cost' => 10000,
        'profit_margin' => 20.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'created_at' => now()->subDays(2),
    ]);

    $exact = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouseB->id,
        'landing_cost' => 12000,
        'profit_margin' => 10.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'created_at' => now()->subDay(),
    ]);

    $stock = $service->getBestStockForContext(
        ProductStock::query()->where('product_id', $product->id)->get(),
        ['postal_code' => '222222']
    );

    expect($stock)->toBe($exact);
});

it('orders stocks by expiry within the same postal code', function () {
    $product = Product::factory()->create();
    $service = app(PriceCalculationService::class);
    $warehouse = makeWarehouse('DL', '333333');

    $laterExpiry = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 10000,
        'profit_margin' => 20.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => now()->addMonths(3),
        'created_at' => now()->subDays(3),
    ]);

    $earlierExpiry = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 12000,
        'profit_margin' => 10.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => now()->addMonth(),
        'created_at' => now()->subDay(),
    ]);

    $stock = $service->getBestStockForContext(
        $product->availableStocks,
        ['postal_code' => '333333']
    );

    expect($stock)->toBe($earlierExpiry);

    $withoutContext = $service->getBestStockForContext(
        $product->availableStocks,
        ['postal_code' => '000000']
    );

    expect($withoutContext)->toBe($laterExpiry);
});

it('prefers non-null expiry before null expiry for the same postal code', function () {
    $product = Product::factory()->create();
    $service = app(PriceCalculationService::class);
    $warehouse = makeWarehouse('MH', '444444');

    $nullExpiry = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 8000,
        'profit_margin' => 25.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => null,
        'created_at' => now()->subDays(5),
    ]);

    $withExpiry = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 9000,
        'profit_margin' => 20.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => now()->addDays(10),
        'created_at' => now()->subDay(),
    ]);

    $stock = $service->getBestStockForContext(
        $product->availableStocks,
        ['postal_code' => '444444']
    );

    expect($stock)->toBe($withExpiry);
});

it('orders by batch number when expiry timestamps match', function () {
    $product = Product::factory()->create();
    $service = app(PriceCalculationService::class);
    $warehouse = makeWarehouse('KA', '555555');
    $expiry = now()->addWeeks(2);

    $batchB = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 11000,
        'profit_margin' => 15.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => $expiry,
        'batch_number' => 'B002',
        'created_at' => now()->subDays(3),
    ]);

    $batchA = ProductStock::factory()->for($product)->create([
        'address_id' => $warehouse->id,
        'landing_cost' => 10000,
        'profit_margin' => 15.0,
        'price' => null,
        'init_quantity' => 10,
        'sold_quantity' => 0,
        'expiry_date' => $expiry,
        'batch_number' => 'A001',
        'created_at' => now()->subDay(),
    ]);

    $stock = $service->getBestStockForContext(
        $product->availableStocks,
        ['postal_code' => '555555']
    );

    expect($stock)->toBe($batchA);
});
