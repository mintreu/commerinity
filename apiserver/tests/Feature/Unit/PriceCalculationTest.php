<?php

declare(strict_types=1);

use App\Models\Address;
use App\Services\Ecommerce\PriceCalculationService;
use App\Services\MoneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->priceService = new PriceCalculationService(new MoneyService());
});

uses(RefreshDatabase::class);

it('calculates percentage off sale price', function () {
    $originalPrice = 10_000;
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'percentage_off', 25.0);

    expect($salePrice)->toBe(7_500);
});

it('calculates fixed amount off sale price', function () {
    $originalPrice = 10_000;
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'fixed_amount_off', 25.0);

    expect($salePrice)->toBe(7_500);
});

it('calculates fixed price sale', function () {
    $originalPrice = 10_000;
    $salePrice = $this->priceService->calculateSalePrice($originalPrice, 'fixed_price', 75.0);

    expect($salePrice)->toBe(7_500);
});

it('determines wholesale quantity thresholds', function () {
    expect($this->priceService->isWholesaleQuantity(50, 25))->toBeTrue();
    expect($this->priceService->isWholesaleQuantity(10, 20))->toBeFalse();
});

it('formats price using MoneyService', function () {
    expect($this->priceService->formatPrice(123_456))->toBe('₹1,234.56');
});

it('calculates discount percent safely', function () {
    $discount = $this->priceService->calculateDiscountPercent(10_000, 7_500);

    expect($discount)->toBe(25.0);
});

it('resolves postal code and state from address context', function () {
    $address = new Address([
        'postal_code' => '560001',
        'state_code' => 'KA',
    ]);

    $context = $this->priceService->resolveStockContext($address);

    expect($context['postal_code'])->toBe('560001')
        ->and($context['state_code'])->toBe('KA');
});
