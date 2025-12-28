<?php

use App\Services\MoneyService;
use Money\Currency;
use Money\Money;

/**
 * Comprehensive MoneyService testing
 * Based on popkult MoneyServiceTest - tests actual implemented functionality
 */
beforeEach(function () {
    $this->moneyService = app(MoneyService::class);
});

// === BASIC FUNCTIONALITY ===

test('initializes with INR currency', function () {
    $currency = $this->moneyService->getCurrency();

    expect($currency)->toBeInstanceOf(Currency::class);
    expect($currency->getCode())->toBe('INR');
});

test('converts rupees to paise correctly', function () {
    expect($this->moneyService->toPaise(100.00))->toBe(10000);
    expect($this->moneyService->toPaise(125.50))->toBe(12550);
    expect($this->moneyService->toPaise(0.99))->toBe(99);
    expect($this->moneyService->toPaise(0.01))->toBe(1);
    expect($this->moneyService->toPaise(0))->toBe(0);
});

test('handles float precision edge cases', function () {
    expect($this->moneyService->toPaise(99.99))->toBe(9999);
    expect($this->moneyService->toPaise(99.9999))->toBe(10000); // Should round up
    expect($this->moneyService->toPaise(99.999))->toBe(10000);  // Should round up
    expect($this->moneyService->toPaise(99.994))->toBe(9999);   // Should round down
});

test('creates Money objects correctly', function () {
    $money = $this->moneyService->fromPaise(15000); // 150.00

    expect($money)->toBeInstanceOf(Money::class);
    expect($money->getAmount())->toBe('15000');
    expect($money->getCurrency()->getCode())->toBe('INR');
});

// === FORMATTING ===

test('formats money as INR currency', function () {
    $money = $this->moneyService->fromPaise(15000);
    $formatted = $this->moneyService->formatMoney($money);

    expect($formatted)->toBe('₹150.00');
});

test('formats paise directly', function () {
    expect($this->moneyService->formatPaise(15000))->toBe('₹150.00');
    expect($this->moneyService->formatPaise(99))->toBe('₹0.99');
    expect($this->moneyService->formatPaise(1))->toBe('₹0.01');
    expect($this->moneyService->formatPaise(0))->toBe('₹0.00');
});

test('formats negative amounts', function () {
    expect($this->moneyService->formatPaise(-15000))->toBe('-₹150.00');
    expect($this->moneyService->formatPaise(-1))->toBe('-₹0.01');
});

test('formats for API responses', function () {
    $apiResponse = $this->moneyService->formatForApi(15000);

    expect($apiResponse)->toHaveKey('paise');
    expect($apiResponse)->toHaveKey('rupees');
    expect($apiResponse)->toHaveKey('formatted');
    expect($apiResponse)->toHaveKey('display_value');

    expect($apiResponse['paise'])->toBe(15000);
    expect($apiResponse['rupees'])->toBe('150.00'); // String to avoid float precision
    expect($apiResponse['formatted'])->toBe('₹150.00');
    expect($apiResponse['display_value'])->toBe('150.00');
});

// === CALCULATIONS ===

test('calculates percentage using MoneyPHP', function () {
    $amount = $this->moneyService->fromPaise(10000); // 100
    $percentage = $amount->multiply(10)->divide(100); // 10%
    expect($percentage->getAmount())->toBe('1000'); // 10

    $amount2 = $this->moneyService->fromPaise(15000); // 150
    $percentage2 = $amount2->multiply(20)->divide(100); // 20%
    expect($percentage2->getAmount())->toBe('3000'); // 30
});

test('adds amounts using MoneyPHP', function () {
    $money1 = $this->moneyService->fromPaise(5000);
    $money2 = $this->moneyService->fromPaise(3000);
    $money3 = $this->moneyService->fromPaise(2000);

    $total = $money1->add($money2)->add($money3);
    expect($total->getAmount())->toBe('10000');

    $single = $this->moneyService->fromPaise(1000);
    expect($single->getAmount())->toBe('1000');
});

test('subtracts amounts using MoneyPHP', function () {
    $money1 = $this->moneyService->fromPaise(10000);
    $money2 = $this->moneyService->fromPaise(3000);
    $money3 = $this->moneyService->fromPaise(2000);

    $result = $money1->subtract($money2)->subtract($money3);
    expect($result->getAmount())->toBe('5000');

    $money4 = $this->moneyService->fromPaise(5000);
    $money5 = $this->moneyService->fromPaise(1000);
    $result2 = $money4->subtract($money5);
    expect($result2->getAmount())->toBe('4000');
});

test('multiplies amounts using MoneyPHP', function () {
    $money1 = $this->moneyService->fromPaise(5000);
    $result1 = $money1->multiply(2);
    expect($result1->getAmount())->toBe('10000');

    $money2 = $this->moneyService->fromPaise(3000);
    $result2 = $money2->multiply(3)->divide(2); // 1.5x = 3/2
    expect($result2->getAmount())->toBe('4500');

    $money3 = $this->moneyService->fromPaise(1000);
    $result3 = $money3->divide(2); // 0.5x = /2
    expect($result3->getAmount())->toBe('500');
});

test('calculates totals using MoneyPHP', function () {
    $money1 = $this->moneyService->fromPaise(5000);
    $money2 = $this->moneyService->fromPaise(3000);
    $money3 = $this->moneyService->fromPaise(2000);
    $money4 = $this->moneyService->fromPaise(1000);

    $total = $money1->add($money2)->add($money3)->add($money4);
    expect($total->getAmount())->toBe('11000');

    $zero = $this->moneyService->zero();
    expect($zero->getAmount())->toBe('0');

    $single = $this->moneyService->fromPaise(5000);
    expect($single->getAmount())->toBe('5000');
});

// === PRECISION & EDGE CASES ===

test('maintains precision with BCMath operations', function () {
    // Test cases that could cause floating-point issues
    expect($this->moneyService->toPaise(99.995))->toBe(10000); // Proper rounding
    expect($this->moneyService->toPaise(99.994))->toBe(9999);  // Proper rounding
    expect($this->moneyService->toPaise(0.005))->toBe(1);      // Rounds up to 1 paise
    expect($this->moneyService->toPaise(0.004))->toBe(0);      // Rounds down
});

test('handles maximum safe values', function () {
    // Test maximum reasonable price values
    $maxPrice = 99999.99; // 99,999.99
    $maxPaise = 9999999;   // 9,999,999 paise

    expect($this->moneyService->toPaise($maxPrice))->toBe($maxPaise);
    expect($this->moneyService->formatPaise($maxPaise))->toBe('₹99,999.99');
});

test('handles zero and null values', function () {
    expect($this->moneyService->toPaise(0))->toBe(0);
    expect($this->moneyService->formatPaise(0))->toBe('₹0.00');

    $money = $this->moneyService->zero();
    expect($money->getAmount())->toBe('0');
    expect($this->moneyService->formatMoney($money))->toBe('₹0.00');
});

test('validates money operations integrity', function () {
    // Test round-trip conversion accuracy
    $originalRupees = 125.50;
    $paise = $this->moneyService->toPaise($originalRupees);
    $money = $this->moneyService->fromPaise($paise);
    $formatted = $this->moneyService->formatMoney($money);

    expect($paise)->toBe(12550);
    expect($formatted)->toBe('₹125.50');
});

// === COMPLEX SCENARIOS ===

test('handles complex financial calculations using MoneyPHP', function () {
    // Simulate a complex e-commerce calculation using MoneyPHP
    $subtotal = $this->moneyService->fromPaise(12550); // 125.50
    $tax = $subtotal->multiply(18)->divide(100); // 18% GST using MoneyPHP
    $discount = $subtotal->multiply(10)->divide(100); // 10% discount using MoneyPHP

    $total = $subtotal->add($tax); // Add using MoneyPHP
    $finalTotal = $total->subtract($discount); // Subtract using MoneyPHP

    expect($tax->getAmount())->toBe('2259');     // 22.59 (18% of 125.50)
    expect($discount->getAmount())->toBe('1255'); // 12.55 (10% of 125.50)
    expect($finalTotal->getAmount())->toBe('13554'); // 135.54

    expect($this->moneyService->formatMoney($finalTotal))->toBe('₹135.54');
});

test('maintains accuracy across multiple operations using MoneyPHP', function () {
    // Chain multiple operations using MoneyPHP
    $amount = $this->moneyService->fromPaise(10000); // 100.00

    $result = $amount
        ->multiply(118)->divide(100)    // Add 18% tax (1.18 = 118/100)
        ->multiply(9)->divide(10)       // Apply 10% discount (0.9 = 9/10)
        ->add($this->moneyService->fromPaise(500)); // Add 5 shipping using MoneyPHP

    // 100 * 1.18 * 0.9 + 5 = 111.20
    expect($result->getAmount())->toBe('11120');
    expect($this->moneyService->formatMoney($result))->toBe('₹111.20');
});

// === STATIC HELPER METHODS ===

test('static format() method works', function () {
    expect(MoneyService::format(15000))->toBe('₹150.00');
    expect(MoneyService::format(99))->toBe('₹0.99');
    expect(MoneyService::format(1))->toBe('₹0.01');
    expect(MoneyService::format(0))->toBe('₹0.00');
    expect(MoneyService::format(null))->toBe('₹0.00');
});

test('static toRupees() converts paise to float', function () {
    expect(MoneyService::toRupees(15000))->toBe(150.00);
    expect(MoneyService::toRupees(1))->toBe(0.01);
    expect(MoneyService::toRupees(0))->toBe(0.0);
    expect(MoneyService::toRupees(null))->toBe(0.0);
});

test('static toRupeesString() converts paise to string', function () {
    expect(MoneyService::toRupeesString(15000))->toBe('150.00');
    expect(MoneyService::toRupeesString(15050))->toBe('150.50');
    expect(MoneyService::toRupeesString(99))->toBe('0.99');
    expect(MoneyService::toRupeesString(1))->toBe('0.01');
    expect(MoneyService::toRupeesString(0))->toBe('0.00');
    expect(MoneyService::toRupeesString(null))->toBe('0.00');
});

test('static toRupeesString() handles negative amounts', function () {
    expect(MoneyService::toRupeesString(-15000))->toBe('-150.00');
    expect(MoneyService::toRupeesString(-1))->toBe('-0.01');
    expect(MoneyService::toRupeesString(-99))->toBe('-0.99');
});

// === IMMUTABLE PATTERN ===

test('make() creates MoneyService from integer', function () {
    $money = MoneyService::make(15000);

    expect($money->getAmount())->toBe(15000);
    expect($money->getCurrencyCode())->toBe('INR');
});

test('make() creates MoneyService from string', function () {
    $money = MoneyService::make('15000');

    expect($money->getAmount())->toBe(15000);
});

test('add() returns new instance (immutable)', function () {
    $money1 = MoneyService::make(5000);
    $result = $money1->add(3000);

    expect($result->getAmount())->toBe(8000);
    expect($money1->getAmount())->toBe(5000); // Original unchanged
});

test('subtract() returns new instance (immutable)', function () {
    $money1 = MoneyService::make(10000);
    $result = $money1->subtract(3000);

    expect($result->getAmount())->toBe(7000);
    expect($money1->getAmount())->toBe(10000); // Original unchanged
});

test('multiply() returns new instance (immutable)', function () {
    $money = MoneyService::make(5000);
    $result = $money->multiply(2);

    expect($result->getAmount())->toBe(10000);
    expect($money->getAmount())->toBe(5000); // Original unchanged
});

test('divide() returns new instance (immutable)', function () {
    $money = MoneyService::make(10000);
    $result = $money->divide(2);

    expect($result->getAmount())->toBe(5000);
    expect($money->getAmount())->toBe(10000); // Original unchanged
});

test('formatted() returns INR currency string', function () {
    $money = MoneyService::make(15000);

    expect($money->formatted())->toBe('₹150.00');
});

test('sameAs() compares correctly', function () {
    $money = MoneyService::make(5000);

    expect($money->sameAs(5000))->toBeTrue();
    expect($money->sameAs(3000))->toBeFalse();
});

test('chaining immutable operations works', function () {
    $money = MoneyService::make(10000);
    $result = $money->add(5000)->subtract(2000)->multiply(2);

    expect($result->getAmount())->toBe(26000);
    expect($money->getAmount())->toBe(10000); // Original unchanged
});
