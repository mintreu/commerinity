<?php

declare(strict_types=1);

use App\Services\MoneyService;
use Money\Currency;
use Money\Money;

/**
 * Comprehensive MoneyService Unit Tests
 *
 * Brutal testing of all functionality:
 * - Factory methods & creation
 * - Conversion (paisa <-> rupees)
 * - Formatting (INR locale)
 * - Arithmetic (immutable & mutable)
 * - Comparisons
 * - Allocation & splitting
 * - Edge cases & precision
 * - Real-world scenarios
 */

// ============================================================================
// FACTORY METHODS & CREATION
// ============================================================================

describe('Factory Methods', function () {
    test('make() creates from integer (paisa)', function () {
        $money = MoneyService::make(15000);

        expect($money->getAmount())->toBe(15000);
        expect($money->getCurrencyCode())->toBe('INR');
    });

    test('make() creates from string paisa', function () {
        $money = MoneyService::make('15000');

        expect($money->getAmount())->toBe(15000);
    });

    test('make() creates from string rupees (with decimal)', function () {
        $money = MoneyService::make('150.50');

        expect($money->getAmount())->toBe(15050);
    });

    test('make() creates from float (treated as rupees)', function () {
        $money = MoneyService::make(150.50);

        expect($money->getAmount())->toBe(15050);
    });

    test('fromPaisa() creates explicitly from paisa', function () {
        $money = MoneyService::fromPaisa(12345);

        expect($money->getAmount())->toBe(12345);
    });

    test('fromRupees() creates from rupees and converts to paisa', function () {
        $money = MoneyService::fromRupees(125.50);

        expect($money->getAmount())->toBe(12550);
    });

    test('fromRupees() handles integer rupees', function () {
        $money = MoneyService::fromRupees(100);

        expect($money->getAmount())->toBe(10000);
    });

    test('fromRupees() handles string rupees', function () {
        $money = MoneyService::fromRupees('99.99');

        expect($money->getAmount())->toBe(9999);
    });

    test('zero() creates zero money', function () {
        $money = MoneyService::zero();

        expect($money->getAmount())->toBe(0);
        expect($money->isZero())->toBeTrue();
    });

    test('fromMoney() wraps existing Money object', function () {
        $phpMoney = new Money(5000, new Currency('INR'));
        $money = MoneyService::fromMoney($phpMoney);

        expect($money->getAmount())->toBe(5000);
    });

    test('handles null input as zero', function () {
        $money = MoneyService::make(null);

        expect($money->getAmount())->toBe(0);
    });

    test('handles empty string as zero', function () {
        $money = MoneyService::make('');

        expect($money->getAmount())->toBe(0);
    });

    test('handles whitespace string as zero', function () {
        $money = MoneyService::make('   ');

        expect($money->getAmount())->toBe(0);
    });

    test('creates from another MoneyService instance', function () {
        $original = MoneyService::make(5000);
        $copy = MoneyService::make($original);

        expect($copy->getAmount())->toBe(5000);
        expect($copy)->not->toBe($original); // Different instances
    });
});

// ============================================================================
// ACCESSORS
// ============================================================================

describe('Accessors', function () {
    test('getAmount() returns integer paisa', function () {
        $money = MoneyService::make(15000);

        expect($money->getAmount())->toBe(15000);
        expect($money->getAmount())->toBeInt();
    });

    test('getAmountString() returns string paisa', function () {
        $money = MoneyService::make(15000);

        expect($money->getAmountString())->toBe('15000');
        expect($money->getAmountString())->toBeString();
    });

    test('getCurrency() returns Currency object', function () {
        $money = MoneyService::make(100);

        expect($money->getCurrency())->toBeInstanceOf(Currency::class);
    });

    test('getCurrencyCode() returns string code', function () {
        $money = MoneyService::make(100);

        expect($money->getCurrencyCode())->toBe('INR');
    });

    test('getMoney() returns underlying Money object', function () {
        $money = MoneyService::make(5000);

        expect($money->getMoney())->toBeInstanceOf(Money::class);
        expect($money->getMoney()->getAmount())->toBe('5000');
    });
});

// ============================================================================
// STATIC CONVERSION HELPERS
// ============================================================================

describe('Conversion Helpers', function () {
    test('toPaisa() converts rupees to paisa correctly', function () {
        expect(MoneyService::toPaisa(100.00))->toBe(10000);
        expect(MoneyService::toPaisa(125.50))->toBe(12550);
        expect(MoneyService::toPaisa(0.99))->toBe(99);
        expect(MoneyService::toPaisa(0.01))->toBe(1);
        expect(MoneyService::toPaisa(0))->toBe(0);
    });

    test('toPaisa() handles float precision edge cases with proper rounding', function () {
        expect(MoneyService::toPaisa(99.99))->toBe(9999);
        expect(MoneyService::toPaisa(99.999))->toBe(10000);  // Rounds up
        expect(MoneyService::toPaisa(99.9949))->toBe(9999);  // Rounds down
        expect(MoneyService::toPaisa(99.995))->toBe(10000);  // Rounds up (0.5 rounds up)
        expect(MoneyService::toPaisa(99.994))->toBe(9999);   // Rounds down
        expect(MoneyService::toPaisa(0.005))->toBe(1);       // Rounds up
        expect(MoneyService::toPaisa(0.004))->toBe(0);       // Rounds down
    });

    test('toRupeesString() converts paisa to rupees string (precision-safe)', function () {
        expect(MoneyService::toRupeesString(15000))->toBe('150.00');
        expect(MoneyService::toRupeesString(15050))->toBe('150.50');
        expect(MoneyService::toRupeesString(99))->toBe('0.99');
        expect(MoneyService::toRupeesString(1))->toBe('0.01');
        expect(MoneyService::toRupeesString(0))->toBe('0.00');
    });

    test('toRupeesString() handles negative amounts', function () {
        expect(MoneyService::toRupeesString(-15000))->toBe('-150.00');
        expect(MoneyService::toRupeesString(-1))->toBe('-0.01');
        expect(MoneyService::toRupeesString(-99))->toBe('-0.99');
    });

    test('toRupees() converts paisa to float', function () {
        expect(MoneyService::toRupees(15000))->toBe(150.00);
        expect(MoneyService::toRupees(1))->toBe(0.01);
    });
});

// ============================================================================
// FORMATTING
// ============================================================================

describe('Formatting', function () {
    test('formatted() returns INR currency string', function () {
        $money = MoneyService::make(15000);

        expect($money->formatted())->toBe('₹150.00');
    });

    test('static format() method works', function () {
        expect(MoneyService::format(15000))->toBe('₹150.00');
        expect(MoneyService::format(99))->toBe('₹0.99');
        expect(MoneyService::format(1))->toBe('₹0.01');
        expect(MoneyService::format(0))->toBe('₹0.00');
    });

    test('formats negative amounts', function () {
        expect(MoneyService::format(-15000))->toBe('-₹150.00');
        expect(MoneyService::format(-1))->toBe('-₹0.01');
    });

    test('formats large amounts with Indian grouping (lakhs, crores)', function () {
        // Indian: 1,50,000 (not 150,000)
        expect(MoneyService::format(15000000))->toBe('₹1,50,000.00');

        // 10 lakhs
        expect(MoneyService::format(100000000))->toBe('₹10,00,000.00');

        // 1 crore
        expect(MoneyService::format(1000000000))->toBe('₹1,00,00,000.00');
    });

    test('formatForApi() returns structured array', function () {
        $money = MoneyService::make(15050);
        $api = $money->formatForApi();

        expect($api)->toHaveKey('paisa');
        expect($api)->toHaveKey('rupees');
        expect($api)->toHaveKey('formatted');
        expect($api)->toHaveKey('display_value');

        expect($api['paisa'])->toBe(15050);
        expect($api['rupees'])->toBe('150.50');
        expect($api['formatted'])->toBe('₹150.50');
        expect($api['display_value'])->toBe('150.50');
    });

    test('formatPaisaForApi() static helper works', function () {
        $api = MoneyService::formatPaisaForApi(12550);

        expect($api['paisa'])->toBe(12550);
        expect($api['rupees'])->toBe('125.50');
    });

    test('formatPlain() returns number without symbol', function () {
        $money = MoneyService::make(15050);

        expect($money->formatPlain())->toBe('150.50');
    });

    test('__toString() returns formatted string', function () {
        $money = MoneyService::make(15000);

        expect((string) $money)->toBe('₹150.00');
    });
});

// ============================================================================
// ARITHMETIC - IMMUTABLE
// ============================================================================

describe('Arithmetic - Immutable', function () {
    test('plus() adds and returns new instance', function () {
        $money1 = MoneyService::make(5000);
        $result = $money1->plus(3000);

        expect($result->getAmount())->toBe(8000);
        expect($money1->getAmount())->toBe(5000); // Original unchanged
    });

    test('plus() accepts MoneyService instance', function () {
        $money1 = MoneyService::make(5000);
        $money2 = MoneyService::make(3000);
        $result = $money1->plus($money2);

        expect($result->getAmount())->toBe(8000);
    });

    test('minus() subtracts and returns new instance', function () {
        $money1 = MoneyService::make(10000);
        $result = $money1->minus(3000);

        expect($result->getAmount())->toBe(7000);
        expect($money1->getAmount())->toBe(10000); // Original unchanged
    });

    test('times() multiplies and returns new instance', function () {
        $money = MoneyService::make(5000);
        $result = $money->times(2);

        expect($result->getAmount())->toBe(10000);
        expect($money->getAmount())->toBe(5000); // Original unchanged
    });

    test('times() handles decimal multipliers', function () {
        $money = MoneyService::make(10000);
        $result = $money->times(1.5);

        expect($result->getAmount())->toBe(15000);
    });

    test('dividedBy() divides and returns new instance', function () {
        $money = MoneyService::make(10000);
        $result = $money->dividedBy(2);

        expect($result->getAmount())->toBe(5000);
        expect($money->getAmount())->toBe(10000); // Original unchanged
    });

    test('dividedBy() throws on division by zero', function () {
        $money = MoneyService::make(10000);

        expect(fn () => $money->dividedBy(0))->toThrow(InvalidArgumentException::class);
    });

    test('percentage() calculates percentage correctly', function () {
        $money = MoneyService::make(10000); // ₹100

        expect($money->percentage(10)->getAmount())->toBe(1000);  // 10% = ₹10
        expect($money->percentage(18)->getAmount())->toBe(1800);  // 18% = ₹18
        expect($money->percentage(5.5)->getAmount())->toBe(550);  // 5.5% = ₹5.50
        expect($money->percentage(100)->getAmount())->toBe(10000); // 100% = ₹100
        expect($money->percentage(0)->getAmount())->toBe(0);      // 0% = ₹0
    });

    test('absolute() returns absolute value', function () {
        $negative = MoneyService::make(-5000);
        $positive = MoneyService::make(5000);

        expect($negative->absolute()->getAmount())->toBe(5000);
        expect($positive->absolute()->getAmount())->toBe(5000);
    });

    test('negate() reverses sign', function () {
        $positive = MoneyService::make(5000);
        $negative = MoneyService::make(-5000);

        expect($positive->negate()->getAmount())->toBe(-5000);
        expect($negative->negate()->getAmount())->toBe(5000);
    });

    test('chaining immutable operations works', function () {
        $money = MoneyService::make(10000);
        $result = $money->plus(5000)->minus(2000)->times(2);

        expect($result->getAmount())->toBe(26000);
        expect($money->getAmount())->toBe(10000); // Original unchanged
    });
});

// ============================================================================
// ARITHMETIC - MUTABLE
// ============================================================================

describe('Arithmetic - Mutable', function () {
    test('add() modifies instance', function () {
        $money = MoneyService::make(5000);
        $money->add(3000);

        expect($money->getAmount())->toBe(8000);
    });

    test('subtract() modifies instance', function () {
        $money = MoneyService::make(10000);
        $money->subtract(3000);

        expect($money->getAmount())->toBe(7000);
    });

    test('multiply() modifies instance', function () {
        $money = MoneyService::make(5000);
        $money->multiply(2);

        expect($money->getAmount())->toBe(10000);
    });

    test('divide() modifies instance', function () {
        $money = MoneyService::make(10000);
        $money->divide(2);

        expect($money->getAmount())->toBe(5000);
    });

    test('divide() throws on division by zero', function () {
        $money = MoneyService::make(10000);

        expect(fn () => $money->divide(0))->toThrow(InvalidArgumentException::class);
    });

    test('chaining mutable operations works', function () {
        $money = MoneyService::make(10000);
        $money->add(5000)->subtract(2000)->multiply(2);

        expect($money->getAmount())->toBe(26000);
    });

    test('mutable returns self for fluent interface', function () {
        $money = MoneyService::make(5000);
        $result = $money->add(1000);

        expect($result)->toBe($money);
    });
});

// ============================================================================
// ALLOCATION & SPLITTING
// ============================================================================

describe('Allocation & Splitting', function () {
    test('allocate() distributes by ratios', function () {
        $money = MoneyService::make(10000); // ₹100
        $parts = $money->allocate([70, 20, 10]);

        expect(count($parts))->toBe(3);
        expect($parts[0]->getAmount())->toBe(7000);  // 70%
        expect($parts[1]->getAmount())->toBe(2000);  // 20%
        expect($parts[2]->getAmount())->toBe(1000);  // 10%
    });

    test('allocate() handles remainders correctly', function () {
        $money = MoneyService::make(100); // ₹1.00 = 100 paisa
        $parts = $money->allocate([1, 1, 1]); // Split 3 ways

        // 100 / 3 = 33.33... so distribution handles remainder
        $total = array_sum(array_map(fn ($p) => $p->getAmount(), $parts));
        expect($total)->toBe(100); // No money lost
    });

    test('split() divides into equal parts', function () {
        $money = MoneyService::make(10000); // ₹100
        $parts = $money->split(4);

        expect(count($parts))->toBe(4);
        expect($parts[0]->getAmount())->toBe(2500);
        expect($parts[1]->getAmount())->toBe(2500);
        expect($parts[2]->getAmount())->toBe(2500);
        expect($parts[3]->getAmount())->toBe(2500);
    });

    test('split() handles uneven splits correctly', function () {
        $money = MoneyService::make(100); // 100 paisa
        $parts = $money->split(3);

        $total = array_sum(array_map(fn ($p) => $p->getAmount(), $parts));
        expect($total)->toBe(100); // No money lost
        expect(count($parts))->toBe(3);
    });

    test('split() throws on invalid parts', function () {
        $money = MoneyService::make(10000);

        expect(fn () => $money->split(0))->toThrow(InvalidArgumentException::class);
    });
});

// ============================================================================
// COMPARISONS
// ============================================================================

describe('Comparisons', function () {
    test('equals() compares correctly', function () {
        $money1 = MoneyService::make(5000);
        $money2 = MoneyService::make(5000);
        $money3 = MoneyService::make(3000);

        expect($money1->equals($money2))->toBeTrue();
        expect($money1->equals($money3))->toBeFalse();
        expect($money1->equals(5000))->toBeTrue();
    });

    test('sameAs() is alias for equals()', function () {
        $money = MoneyService::make(5000);

        expect($money->sameAs(5000))->toBeTrue();
        expect($money->sameAs(3000))->toBeFalse();
    });

    test('greaterThan() compares correctly', function () {
        $money1 = MoneyService::make(5000);
        $money2 = MoneyService::make(3000);

        expect($money1->greaterThan($money2))->toBeTrue();
        expect($money2->greaterThan($money1))->toBeFalse();
        expect($money1->greaterThan(5000))->toBeFalse(); // Equal, not greater
    });

    test('greaterThanOrEqual() compares correctly', function () {
        $money = MoneyService::make(5000);

        expect($money->greaterThanOrEqual(5000))->toBeTrue();
        expect($money->greaterThanOrEqual(3000))->toBeTrue();
        expect($money->greaterThanOrEqual(7000))->toBeFalse();
    });

    test('lessThan() compares correctly', function () {
        $money1 = MoneyService::make(3000);
        $money2 = MoneyService::make(5000);

        expect($money1->lessThan($money2))->toBeTrue();
        expect($money2->lessThan($money1))->toBeFalse();
    });

    test('lessThanOrEqual() compares correctly', function () {
        $money = MoneyService::make(5000);

        expect($money->lessThanOrEqual(5000))->toBeTrue();
        expect($money->lessThanOrEqual(7000))->toBeTrue();
        expect($money->lessThanOrEqual(3000))->toBeFalse();
    });

    test('compare() returns -1, 0, or 1', function () {
        $money = MoneyService::make(5000);

        expect($money->compare(3000))->toBe(1);  // Greater
        expect($money->compare(5000))->toBe(0);  // Equal
        expect($money->compare(7000))->toBe(-1); // Less
    });

    test('isZero() checks correctly', function () {
        expect(MoneyService::make(0)->isZero())->toBeTrue();
        expect(MoneyService::make(1)->isZero())->toBeFalse();
        expect(MoneyService::make(-1)->isZero())->toBeFalse();
    });

    test('isPositive() checks correctly', function () {
        expect(MoneyService::make(1000)->isPositive())->toBeTrue();
        expect(MoneyService::make(0)->isPositive())->toBeFalse();
        expect(MoneyService::make(-1000)->isPositive())->toBeFalse();
    });

    test('isNegative() checks correctly', function () {
        expect(MoneyService::make(-1000)->isNegative())->toBeTrue();
        expect(MoneyService::make(0)->isNegative())->toBeFalse();
        expect(MoneyService::make(1000)->isNegative())->toBeFalse();
    });

    test('isSameCurrency() checks correctly', function () {
        $inr1 = MoneyService::make(1000, 'INR');
        $inr2 = MoneyService::make(2000, 'INR');
        $usd = MoneyService::make(1000, 'USD');

        expect($inr1->isSameCurrency($inr2))->toBeTrue();
        expect($inr1->isSameCurrency($usd))->toBeFalse();
    });
});

// ============================================================================
// UTILITY METHODS
// ============================================================================

describe('Utility Methods', function () {
    test('min() returns smaller amount', function () {
        $money = MoneyService::make(5000);

        expect($money->min(3000)->getAmount())->toBe(3000);
        expect($money->min(7000)->getAmount())->toBe(5000);
        expect($money->min(5000)->getAmount())->toBe(5000);
    });

    test('max() returns larger amount', function () {
        $money = MoneyService::make(5000);

        expect($money->max(3000)->getAmount())->toBe(5000);
        expect($money->max(7000)->getAmount())->toBe(7000);
        expect($money->max(5000)->getAmount())->toBe(5000);
    });

    test('ratioOf() calculates ratio', function () {
        $money1 = MoneyService::make(10000);
        $money2 = MoneyService::make(5000);

        expect($money1->ratioOf($money2))->toBe('2');
    });

    test('copy() creates independent clone', function () {
        $original = MoneyService::make(5000);
        $copy = $original->copy();

        expect($copy->getAmount())->toBe(5000);

        $copy->add(1000);
        expect($copy->getAmount())->toBe(6000);
        expect($original->getAmount())->toBe(5000); // Original unchanged
    });
});

// ============================================================================
// EDGE CASES & PRECISION
// ============================================================================

describe('Edge Cases & Precision', function () {
    test('handles very small amounts', function () {
        $money = MoneyService::make(1); // 1 paisa

        expect($money->getAmount())->toBe(1);
        expect($money->formatted())->toBe('₹0.01');
    });

    test('handles large amounts without overflow', function () {
        // 10 crore rupees
        $money = MoneyService::make(10000000000);

        expect($money->getAmount())->toBe(10000000000);
        expect($money->formatted())->toContain('₹');
    });

    test('maintains precision through multiple operations', function () {
        $money = MoneyService::make(10000); // ₹100

        // Complex calculation: +18% tax, -10% discount
        $withTax = $money->plus($money->percentage(18));     // ₹118
        $final = $withTax->minus($withTax->percentage(10));  // ₹106.20

        expect($final->getAmount())->toBe(10620);
        expect($final->formatted())->toBe('₹106.20');
    });

    test('round-trip conversion maintains precision', function () {
        $originalRupees = 125.50;
        $paisa = MoneyService::toPaisa($originalRupees);
        $money = MoneyService::fromPaisa($paisa);
        $rupeesString = $money->formatPlain();

        expect($paisa)->toBe(12550);
        expect($rupeesString)->toBe('125.50');
    });

    test('negative arithmetic works correctly', function () {
        $positive = MoneyService::make(5000);
        $negative = MoneyService::make(-3000);

        $result = $positive->plus($negative);
        expect($result->getAmount())->toBe(2000);

        $result2 = $positive->minus($negative);
        expect($result2->getAmount())->toBe(8000); // 5000 - (-3000) = 8000
    });
});

// ============================================================================
// REAL-WORLD SCENARIOS
// ============================================================================

describe('Real-World Scenarios', function () {
    test('e-commerce order calculation', function () {
        // Order: ₹125.50 subtotal, 18% GST, 10% discount, ₹50 shipping
        $subtotal = MoneyService::fromRupees(125.50);
        $gst = $subtotal->percentage(18);
        $discount = $subtotal->percentage(10);
        $shipping = MoneyService::fromRupees(50);

        $total = $subtotal
            ->plus($gst)
            ->minus($discount)
            ->plus($shipping);

        // 125.50 + 22.59 - 12.55 + 50 = 185.54
        expect($subtotal->getAmount())->toBe(12550);
        expect($gst->getAmount())->toBe(2259);
        expect($discount->getAmount())->toBe(1255);
        expect($total->getAmount())->toBe(18554);
        expect($total->formatted())->toBe('₹185.54');
    });

    test('MLM commission distribution', function () {
        // Total commission: ₹1000, split: 70% direct, 20% upline, 10% platform
        $totalCommission = MoneyService::fromRupees(1000);
        $parts = $totalCommission->allocate([70, 20, 10]);

        expect($parts[0]->getAmount())->toBe(70000); // ₹700
        expect($parts[1]->getAmount())->toBe(20000); // ₹200
        expect($parts[2]->getAmount())->toBe(10000); // ₹100

        // Verify no money lost
        $total = $parts[0]->getAmount() + $parts[1]->getAmount() + $parts[2]->getAmount();
        expect($total)->toBe(100000);
    });

    test('wallet balance check', function () {
        $balance = MoneyService::fromRupees(500);
        $purchaseAmount = MoneyService::fromRupees(300);
        $insufficientAmount = MoneyService::fromRupees(700);

        expect($balance->greaterThanOrEqual($purchaseAmount))->toBeTrue();
        expect($balance->greaterThanOrEqual($insufficientAmount))->toBeFalse();

        $remaining = $balance->minus($purchaseAmount);
        expect($remaining->getAmount())->toBe(20000); // ₹200
    });

    test('subscription pricing tiers', function () {
        $monthlyPrice = MoneyService::fromRupees(299);
        // Quarterly: 3 months with 10% discount on monthly price deducted
        $quarterlyPrice = $monthlyPrice->times(3)->minus($monthlyPrice->percentage(10));
        // Yearly: 12 months with 20% discount on yearly total
        $yearlyPrice = $monthlyPrice->times(12)->minus($monthlyPrice->times(12)->percentage(20));

        expect($monthlyPrice->getAmount())->toBe(29900);
        // 299*3 = 897, minus 29.90 (10% of 299) = 867.10
        expect($quarterlyPrice->getAmount())->toBe(86710);
        // 299*12 = 3588, minus 717.60 (20% of 3588) = 2870.40
        expect($yearlyPrice->getAmount())->toBe(287040);
    });

    test('refund with partial amount', function () {
        $originalPayment = MoneyService::fromRupees(999);
        $partialRefundPercent = 75;

        $refundAmount = $originalPayment->percentage($partialRefundPercent);

        expect($refundAmount->getAmount())->toBe(74925); // ₹749.25
        expect($refundAmount->formatted())->toBe('₹749.25');
    });

    test('API response formatting for frontend', function () {
        $balance = MoneyService::fromRupees(15000.50);
        $api = $balance->formatForApi();

        // Frontend can safely use string values
        expect($api['rupees'])->toBe('15000.50');
        expect($api['display_value'])->toBe('15000.50');
        expect($api['formatted'])->toContain('₹');

        // Backend stores integer
        expect($api['paisa'])->toBe(1500050);
    });
});
