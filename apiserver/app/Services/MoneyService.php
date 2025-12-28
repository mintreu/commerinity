<?php

namespace App\Services;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money as MoneyPHP;
use NumberFormatter;

/**
 * Money Service Implementation
 *
 * Provides safe money operations with MoneyPHP integration.
 * Supports both new immutable pattern and legacy API for backward compatibility.
 */
final class MoneyService
{
    private readonly MoneyPHP $money;

    private Currency $currency;

    public function __construct(MoneyPHP|string|int $value = 0)
    {
        $this->currency = new Currency('INR');

        if ($value instanceof MoneyPHP) {
            $this->money = $value;
        } else {
            $amount = is_string($value) ? (int) $value : $value;
            $this->money = new MoneyPHP($amount, $this->currency);
        }
    }

    public static function make(MoneyPHP|string|int $value = 0): self
    {
        return new self($value);
    }

    // ========================================
    // LEGACY API (for backward compatibility)
    // ========================================

    /**
     * Convert rupees to paise
     */
    public function toPaise(float|int $rupees): int
    {
        // Use round() to handle floating point precision
        return (int) round($rupees * 100);
    }

    /**
     * Convert paise to Money object
     */
    public function fromPaise(int $paise): MoneyPHP
    {
        return new MoneyPHP($paise, $this->currency);
    }

    /**
     * Create Money from rupees string (precision-safe)
     */
    public function fromRupees(string|float|int $rupees): MoneyPHP
    {
        $paise = $this->toPaise((float) $rupees);

        return new MoneyPHP($paise, $this->currency);
    }

    /**
     * Format Money using MoneyPHP's IntlMoneyFormatter
     */
    public function formatMoney(MoneyPHP $money): string
    {
        static $formatter = null;

        if ($formatter === null) {
            $numberFormatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
            $formatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);
        }

        return $formatter->format($money);
    }

    /**
     * Format paise directly (convenience method)
     */
    public function formatPaise(int $paise): string
    {
        return $this->formatMoney(new MoneyPHP($paise, $this->currency));
    }

    /**
     * Format paisa as INR currency string (static helper)
     */
    public static function format(int|string|null $paise): string
    {
        if ($paise === null || $paise === '') {
            return '₹0.00';
        }

        return (new self((int) $paise))->formatted();
    }

    /**
     * Get zero money
     */
    public function zero(): MoneyPHP
    {
        return new MoneyPHP(0, $this->currency);
    }

    /**
     * Format for API responses (precision-safe, no floats)
     */
    public function formatForApi(int $paise): array
    {
        $money = new MoneyPHP($paise, $this->currency);

        // Use integer math for rupees calculation
        $negative = $paise < 0;
        $absPaise = abs($paise);
        $rupeesInt = intdiv($absPaise, 100);
        $remainder = $absPaise % 100;

        $rupeesString = ($negative ? '-' : '').$rupeesInt.'.'.str_pad((string) $remainder, 2, '0', STR_PAD_LEFT);

        return [
            'paise' => $paise,
            'rupees' => $rupeesString, // String, not float!
            'formatted' => $this->formatMoney($money),
            'display_value' => $rupeesString,
        ];
    }

    // ========================================
    // NEW IMMUTABLE PATTERN
    // ========================================

    public function add(self|int $other): self
    {
        $otherMoney = $other instanceof self
            ? $other->money
            : new MoneyPHP($other, $this->money->getCurrency());

        return new self($this->money->add($otherMoney));
    }

    public function subtract(self|int $other): self
    {
        $otherMoney = $other instanceof self
            ? $other->money
            : new MoneyPHP($other, $this->money->getCurrency());

        return new self($this->money->subtract($otherMoney));
    }

    public function multiply(int|float $multiplier): self
    {
        return new self($this->money->multiply((string) $multiplier));
    }

    public function divide(int|float $divisor): self
    {
        return new self($this->money->divide((string) $divisor));
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getAmount(): int
    {
        return (int) $this->money->getAmount();
    }

    public function getCurrency(): Currency
    {
        return $this->money->getCurrency();
    }

    public function getCurrencyCode(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    // ========================================
    // COMPARISON
    // ========================================

    public function sameAs(self|string|int $value): bool
    {
        return $value instanceof self
            ? $this->money->equals($value->money)
            : $this->money->equals((new self($value))->money);
    }

    // ========================================
    // FORMATTING
    // ========================================

    public function formatted(): string
    {
        // Static caching: Cache the expensive formatter objects to avoid recreating them on every call
        // NumberFormatter and IntlMoneyFormatter creation has significant overhead
        static $formatter = null;

        if ($formatter === null) {
            $numberFormatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
            $formatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);
        }

        return $formatter->format($this->money);
    }

    public static function formatStatic(int|string|null $value): string
    {
        if ($value === null) {
            return '₹0.00';
        }

        $money = new self($value);

        return $money->formatted();
    }

    /**
     * Convert paise to rupees as float
     * Note: Use toRupeesString() for display to avoid float precision issues
     */
    public static function toRupees(int|string|null $paise): float
    {
        if ($paise === null || $paise === '') {
            return 0.0;
        }

        return (int) $paise / 100;
    }

    /**
     * Convert paise to rupees as string (precision-safe for display)
     */
    public static function toRupeesString(int|string|null $paise): string
    {
        if ($paise === null || $paise === '') {
            return '0.00';
        }

        $amount = (int) $paise;
        $negative = $amount < 0;
        $absAmount = abs($amount);
        $rupees = intdiv($absAmount, 100);
        $remainder = $absAmount % 100;

        return ($negative ? '-' : '').$rupees.'.'.str_pad((string) $remainder, 2, '0', STR_PAD_LEFT);
    }
}
