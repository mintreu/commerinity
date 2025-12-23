<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

/**
 * Money Service - Enterprise-grade precision-safe money handling
 *
 * Built on PHP Money library for financial accuracy.
 * All monetary values are stored as integers (paisa - smallest currency unit).
 *
 * Features:
 * - Precision-safe arithmetic (no floating-point errors)
 * - Indian locale formatting (₹1,50,000.00)
 * - Both mutable and immutable operations
 * - Comparison operators
 * - Percentage calculations
 * - API-ready formatting
 * - Allocation/splitting for fee distribution
 *
 * Usage:
 * - Store in DB: integer (paisa)
 * - Model cast: 'integer' (NOT MoneyCast)
 * - Format: MoneyService::format($paisa)
 * - Create: MoneyService::make($paisa) or MoneyService::fromRupees(150.50)
 *
 * @see https://www.moneyphp.org/
 */
final class MoneyService
{
    private Money $money;

    private Currency $currency;

    /**
     * Create a new MoneyService instance.
     *
     * @param  self|Money|string|int|float|null  $amount  Amount in paisa (int/string) or rupees (float)
     * @param  string|null  $currency  Currency code (defaults to INR)
     */
    public function __construct(self|Money|string|int|float|null $amount = 0, ?string $currency = null)
    {
        $this->currency = new Currency($currency ?? self::defaultCurrency());
        $resolvedAmount = $this->resolveInputAmount($amount);
        $this->money = new Money($resolvedAmount, $this->currency);
    }

    // ========================================
    // STATIC FACTORY METHODS
    // ========================================

    /**
     * Create instance from any supported type.
     */
    public static function make(self|Money|string|int|float|null $amount = 0, ?string $currency = null): self
    {
        return new self($amount, $currency);
    }

    /**
     * Create from paisa (smallest unit - explicit).
     */
    public static function fromPaisa(int $paisa, ?string $currency = null): self
    {
        return new self($paisa, $currency);
    }

    /**
     * Create from rupees (will convert to paisa).
     */
    public static function fromRupees(float|int|string $rupees, ?string $currency = null): self
    {
        $paisa = self::toPaisa((float) $rupees);

        return new self($paisa, $currency);
    }

    /**
     * Create zero money instance.
     */
    public static function zero(?string $currency = null): self
    {
        return new self(0, $currency);
    }

    /**
     * Create from Money object.
     */
    public static function fromMoney(Money $money): self
    {
        return new self($money);
    }

    /**
     * Get default currency code (from config or INR).
     */
    public static function defaultCurrency(): string
    {
        return config('app.currency', 'INR');
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get amount in paisa as integer.
     */
    public function getAmount(): int
    {
        return (int) $this->money->getAmount();
    }

    /**
     * Get amount as string (for BC math precision).
     */
    public function getAmountString(): string
    {
        return $this->money->getAmount();
    }

    /**
     * Get currency object.
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Get currency code string.
     */
    public function getCurrencyCode(): string
    {
        return $this->currency->getCode();
    }

    /**
     * Get underlying MoneyPHP Money object.
     */
    public function getMoney(): Money
    {
        return $this->money;
    }

    // ========================================
    // STATIC CONVERSION HELPERS
    // ========================================

    /**
     * Convert rupees to paisa (precision-safe using round).
     */
    public static function toPaisa(float|int $rupees): int
    {
        return (int) round($rupees * 100);
    }

    /**
     * Convert paisa to rupees as string (precision-safe, no float output).
     * Use this for API responses instead of float.
     */
    public static function toRupeesString(int $paisa): string
    {
        $negative = $paisa < 0;
        $absPaisa = abs($paisa);
        $rupeesInt = intdiv($absPaisa, 100);
        $remainder = $absPaisa % 100;

        return ($negative ? '-' : '').$rupeesInt.'.'.str_pad((string) $remainder, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Convert paisa to rupees float (use carefully - precision loss possible).
     */
    public static function toRupees(int $paisa): float
    {
        return $paisa / 100;
    }

    // ========================================
    // FORMATTING
    // ========================================

    /**
     * Format as localized currency string (e.g., "₹1,50,000.00").
     */
    public function formatted(string $locale = 'en_IN'): string
    {
        return $this->getFormatter($locale)->format($this->money);
    }

    /**
     * Static format helper - quick formatting without creating instance.
     */
    public static function format(self|Money|string|int|float|null $amount = 0, ?string $currency = null, string $locale = 'en_IN'): string
    {
        return self::make($amount, $currency)->formatted($locale);
    }

    /**
     * Format for API responses (precision-safe array with no floats).
     */
    public function formatForApi(): array
    {
        $paisa = $this->getAmount();
        $rupeesString = self::toRupeesString($paisa);

        return [
            'paisa' => $paisa,
            'rupees' => $rupeesString,
            'formatted' => $this->formatted(),
            'display_value' => $rupeesString,
        ];
    }

    /**
     * Static API format helper.
     */
    public static function formatPaisaForApi(int $paisa, ?string $currency = null): array
    {
        return self::fromPaisa($paisa, $currency)->formatForApi();
    }

    /**
     * Format without currency symbol (plain number).
     */
    public function formatPlain(): string
    {
        return self::toRupeesString($this->getAmount());
    }

    /**
     * Magic toString - returns formatted string.
     */
    public function __toString(): string
    {
        return $this->formatted();
    }

    // ========================================
    // ARITHMETIC - IMMUTABLE (returns new instance, original unchanged)
    // ========================================

    /**
     * Add amount - immutable (returns new instance).
     */
    public function plus(self|Money|string|int|float|null $amount = 0, ?string $currency = null): self
    {
        $other = $this->resolveMoneyOperand($amount, $currency);

        return new self($this->money->add($other));
    }

    /**
     * Subtract amount - immutable (returns new instance).
     */
    public function minus(self|Money|string|int|float|null $amount = 0, ?string $currency = null): self
    {
        $other = $this->resolveMoneyOperand($amount, $currency);

        return new self($this->money->subtract($other));
    }

    /**
     * Multiply by factor - immutable (returns new instance).
     */
    public function times(int|float|string $factor): self
    {
        return new self($this->money->multiply((string) $factor));
    }

    /**
     * Divide by factor - immutable (returns new instance).
     */
    public function dividedBy(int|float|string $divisor): self
    {
        if ((float) $divisor == 0) {
            throw new InvalidArgumentException('Division by zero');
        }

        return new self($this->money->divide((string) $divisor));
    }

    /**
     * Calculate percentage - immutable.
     * Example: $money->percentage(18) returns 18% of the amount.
     */
    public function percentage(int|float $percent): self
    {
        return $this->times($percent)->dividedBy(100);
    }

    /**
     * Get absolute value - immutable.
     */
    public function absolute(): self
    {
        return new self($this->money->absolute());
    }

    /**
     * Negate (reverse sign) - immutable.
     */
    public function negate(): self
    {
        return new self($this->money->negative());
    }

    // ========================================
    // ARITHMETIC - MUTABLE (modifies this instance)
    // ========================================

    /**
     * Add amount - mutable (modifies this instance).
     */
    public function add(self|Money|string|int|float|null $amount = 0, ?string $currency = null): self
    {
        $other = $this->resolveMoneyOperand($amount, $currency);
        $this->money = $this->money->add($other);

        return $this;
    }

    /**
     * Subtract amount - mutable (modifies this instance).
     */
    public function subtract(self|Money|string|int|float|null $amount = 0, ?string $currency = null): self
    {
        $other = $this->resolveMoneyOperand($amount, $currency);
        $this->money = $this->money->subtract($other);

        return $this;
    }

    /**
     * Multiply by factor - mutable (modifies this instance).
     */
    public function multiply(int|float|string $factor): self
    {
        $this->money = $this->money->multiply((string) $factor);

        return $this;
    }

    /**
     * Divide by factor - mutable (modifies this instance).
     */
    public function divide(int|float|string $divisor): self
    {
        if ((float) $divisor == 0) {
            throw new InvalidArgumentException('Division by zero');
        }
        $this->money = $this->money->divide((string) $divisor);

        return $this;
    }

    // ========================================
    // ALLOCATION & SPLITTING
    // ========================================

    /**
     * Allocate amount according to ratios (fee distribution).
     * Example: $100->allocate([70, 20, 10]) = [70, 20, 10]
     *
     * @param  array<int>  $ratios
     * @return array<self>
     */
    public function allocate(array $ratios): array
    {
        $allocated = $this->money->allocate($ratios);

        return array_map(fn (Money $m) => new self($m), $allocated);
    }

    /**
     * Split into N equal parts (handles remainders properly).
     *
     * @return array<self>
     */
    public function split(int $parts): array
    {
        if ($parts < 1) {
            throw new InvalidArgumentException('Cannot split into less than 1 part');
        }

        $allocated = $this->money->allocateTo($parts);

        return array_map(fn (Money $m) => new self($m), $allocated);
    }

    // ========================================
    // COMPARISONS
    // ========================================

    /**
     * Check if equal to another amount.
     */
    public function equals(self|int $other): bool
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->equals($otherMoney);
    }

    /**
     * Alias for equals.
     */
    public function sameAs(self|int $other): bool
    {
        return $this->equals($other);
    }

    /**
     * Check if greater than another amount.
     */
    public function greaterThan(self|int $other): bool
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->greaterThan($otherMoney);
    }

    /**
     * Check if greater than or equal to another amount.
     */
    public function greaterThanOrEqual(self|int $other): bool
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->greaterThanOrEqual($otherMoney);
    }

    /**
     * Check if less than another amount.
     */
    public function lessThan(self|int $other): bool
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->lessThan($otherMoney);
    }

    /**
     * Check if less than or equal to another amount.
     */
    public function lessThanOrEqual(self|int $other): bool
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->lessThanOrEqual($otherMoney);
    }

    /**
     * Compare to another amount. Returns -1, 0, or 1.
     */
    public function compare(self|int $other): int
    {
        $otherMoney = $this->resolveComparisonOperand($other);

        return $this->money->compare($otherMoney);
    }

    /**
     * Check if amount is zero.
     */
    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    /**
     * Check if amount is positive (> 0).
     */
    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    /**
     * Check if amount is negative (< 0).
     */
    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    /**
     * Check if has same currency as another.
     */
    public function isSameCurrency(self $other): bool
    {
        return $this->money->isSameCurrency($other->money);
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get min of this and another amount.
     */
    public function min(self|int $other): self
    {
        $otherInstance = $other instanceof self ? $other : self::make($other);

        return $this->lessThanOrEqual($otherInstance) ? clone $this : clone $otherInstance;
    }

    /**
     * Get max of this and another amount.
     */
    public function max(self|int $other): self
    {
        $otherInstance = $other instanceof self ? $other : self::make($other);

        return $this->greaterThanOrEqual($otherInstance) ? clone $this : clone $otherInstance;
    }

    /**
     * Get the ratio/quotient between this and another amount.
     */
    public function ratioOf(self $other): string
    {
        $ratio = $this->money->ratioOf($other->money);

        // Return clean integer string if it's a whole number
        if (str_contains($ratio, '.')) {
            $ratio = rtrim(rtrim($ratio, '0'), '.');
        }

        return $ratio;
    }

    /**
     * Clone this instance.
     */
    public function copy(): self
    {
        return clone $this;
    }

    // ========================================
    // STATIC CALCULATION HELPERS
    // ========================================

    /**
     * Static sum of multiple amounts.
     *
     * @param  array<self|int>  $amounts
     */
    public static function sum(array $amounts, ?string $currency = null): self
    {
        $result = self::zero($currency);
        foreach ($amounts as $amount) {
            $result->add($amount);
        }

        return $result;
    }

    /**
     * Static subtraction helper.
     */
    public static function diff(self|int $minuend, self|int $subtrahend, ?string $currency = null): self
    {
        return self::make($minuend, $currency)->minus($subtrahend);
    }

    /**
     * Static multiplication helper.
     */
    public static function product(self|int $amount, int|float|string $factor, ?string $currency = null): self
    {
        return self::make($amount, $currency)->times($factor);
    }

    /**
     * Static division helper.
     */
    public static function quotient(self|int $amount, int|float|string $divisor, ?string $currency = null): self
    {
        return self::make($amount, $currency)->dividedBy($divisor);
    }

    /**
     * Static percentage helper.
     */
    public static function percentOf(self|int $amount, int|float $percent, ?string $currency = null): self
    {
        return self::make($amount, $currency)->percentage($percent);
    }

    /**
     * Add percentage to amount (e.g., add 18% GST).
     */
    public function addPercentage(int|float $percent): self
    {
        return $this->plus($this->percentage($percent));
    }

    /**
     * Subtract percentage from amount (e.g., 10% discount).
     */
    public function subtractPercentage(int|float $percent): self
    {
        return $this->minus($this->percentage($percent));
    }

    /**
     * Get percentage of total (what % is this of another amount).
     */
    public function percentageOf(self|int $total): float
    {
        $totalAmount = $total instanceof self ? $total->getAmount() : $total;
        if ($totalAmount === 0) {
            return 0.0;
        }

        return ($this->getAmount() / $totalAmount) * 100;
    }

    // ========================================
    // PRIVATE HELPERS
    // ========================================

    /**
     * Resolve input to paisa integer.
     */
    private function resolveInputAmount(self|Money|string|int|float|null $amount): int
    {
        if ($amount === null) {
            return 0;
        }

        if ($amount instanceof self) {
            return $amount->getAmount();
        }

        if ($amount instanceof Money) {
            return (int) $amount->getAmount();
        }

        if (is_float($amount)) {
            // Float treated as rupees
            return self::toPaisa($amount);
        }

        if (is_string($amount)) {
            $trimmed = trim($amount);
            if ($trimmed === '') {
                return 0;
            }
            // Contains decimal = rupees, otherwise = paisa
            if (str_contains($trimmed, '.')) {
                return self::toPaisa((float) $trimmed);
            }

            return (int) $trimmed;
        }

        return (int) $amount;
    }

    /**
     * Resolve operand for arithmetic operations.
     */
    private function resolveMoneyOperand(self|Money|string|int|float|null $amount, ?string $currency): Money
    {
        if ($amount instanceof self) {
            return $amount->money;
        }

        return self::make($amount, $currency ?? $this->getCurrencyCode())->money;
    }

    /**
     * Resolve operand for comparison operations.
     */
    private function resolveComparisonOperand(self|int $other): Money
    {
        return $other instanceof self ? $other->money : self::make($other)->money;
    }

    /**
     * Get cached IntlMoneyFormatter for locale.
     */
    private function getFormatter(string $locale = 'en_IN'): IntlMoneyFormatter
    {
        static $formatters = [];

        if (! isset($formatters[$locale])) {
            $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatters[$locale] = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);
        }

        return $formatters[$locale];
    }
}
