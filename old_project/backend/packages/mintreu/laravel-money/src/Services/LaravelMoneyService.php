<?php

namespace Mintreu\LaravelMoney\Services;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use function PHPUnit\Framework\stringContains;

class LaravelMoneyService
{

    private Money $money;
    private Currency $currency;
    private $formatter;

     /**
      * @param LaravelMoneyService|Money|string|int|float|null $amount
      * @param string|null $currency
      */
    public function __construct(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null)
    {
        $amount = $this->resolveInputAmount($amount);
        $this->currency = new Currency($currency ?? self::defaultCurrency());
        $this->money = new Money($amount, $this->currency);
    }

    public static function make(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null)
    {
        return new static($amount,$currency);
    }

    public static function defaultCurrency(): string
    {
        return config('laravel-money.currency');
    }

    public function getCurrency(): Currency
    {
        return $this->money->getCurrency();
    }
    public function getCurrencyCode(): string
    {
        return $this->getCurrency()->getCode();
    }
    public function getAmount(): string
    {
        return $this->money->getAmount();
    }


    public function formatted(string $locale = 'en_IN')
    {
        $this->resolveFormatter($locale);
        return $this->formatter->format($this->money);
    }


    public function __toString(): string
    {
        return $this->formatted();
    }


    public static function format(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null,string $locale = 'en_IN')
    {
        $instance = self::make($amount,$currency);
        return $instance->formatted($locale);
    }







     // ========================================
     // ARITHMETIC - MUTABLE
     // ========================================

    public function add(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null): static
    {
        $givenMoney =  self::make($amount,$currency)->money;
        $this->money = $this->money->add($givenMoney);
        return $this;
    }

    public function plus(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null): LaravelMoneyService|static
    {
        $givenMoney = self::make($amount,$currency)->money;
        $newInstance = clone $this;
        $newInstance->money = $newInstance->money->add($givenMoney);
        return $newInstance;
    }


    public function subtract(LaravelMoneyService|Money|string|int|float|null $amount = 0,?string $currency = null): static
    {
        $givenMoney = self::make($amount,$currency)->money;
        $this->money = $this->money->subtract($givenMoney);
        return $this;
    }

    public function minus(LaravelMoneyService|Money|string|int|float|null $amount = 0, ?string $currency = null): LaravelMoneyService|static
    {
        $givenMoney = self::make($amount,$currency)->money;
        $newInstance = clone $this;
        $newInstance->money = $newInstance->money->subtract($givenMoney);
        return $newInstance;
    }


    public function multiply(LaravelMoneyService|Money|string|int|float $factor = 0, ?string $currency = null): static
    {
        $givenMoney = self::make($factor,$currency);
        $factor = $givenMoney->getAmount();
        $this->money = $this->money->multiply($factor);
        return $this;
    }

    public function times(LaravelMoneyService|Money|string|int|float $factor = 0, ?string $currency = null): LaravelMoneyService|static
    {
        $givenMoney = self::make($factor,$currency);
        $factor = $givenMoney->getAmount();
        $newInstance = clone $this;
        $newInstance->money = $newInstance->money->multiply($factor);
        return $newInstance;
    }


    public function divide(LaravelMoneyService|Money|string|int|float $factor = 0, ?string $currency = null): static
    {
        $givenMoney = self::make($factor,$currency);
        $factor = $givenMoney->getAmount();
        $this->money = $this->money->divide($factor);
        return $this;
    }

    public function dividedBy(LaravelMoneyService|Money|string|int|float $factor = 0, ?string $currency = null): LaravelMoneyService|static
    {
        $givenMoney = self::make($factor,$currency);
        $factor = $givenMoney->getAmount();
        $newInstance = clone $this;
        $newInstance->money = $newInstance->money->divide($factor);
        return $newInstance;
    }




    // ========================================
    // COMPARISONS (UNCHANGED - ALREADY SAFE)
    // ========================================

    public function equals(self|int $other): bool
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->equals($other->money);
    }

    public function sameAs(self|int $other): bool
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->equals($other);
    }

    public function greaterThan(self|int $other): bool
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->greaterThan($other->money);
    }

    public function greaterThanOrEqual(self|int $other): bool
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->greaterThanOrEqual($other->money);
    }

    public function lessThan(self|int $other): bool
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->lessThan($other->money);
    }

    public function lessThanOrEqual(self|int $other)
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->lessThanOrEqual($other->money);
    }

    public function compare(self|int $other): int
    {
        $other = is_int($other) ? self::make($other) : $other;
        return $this->money->compare($other->money);
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }




    // PRIVATE FUNCTIONS

    private function resolveInputAmount(LaravelMoneyService|Money|string|int|float|null $amount):int
    {
        if (is_float($amount))
        {
            $amount = $amount * 100;
        }

        if (is_string($amount)) {
            $trimmed = trim($amount);
            if ($trimmed === '') {
                return 0;
            }
            $amount = str_contains($trimmed,'.') ? (float) $trimmed * 100 : (float) $trimmed;
        }

        if ($amount instanceof LaravelMoneyService || $amount instanceof Money)
        {
            return $amount->getAmount();
        }

        return $amount ?? 0;
    }


    private function resolveFormatter(string $locale = 'en_IN'): void
    {
        $this->formatter = new IntlMoneyFormatter(
            new NumberFormatter($locale, NumberFormatter::CURRENCY),new ISOCurrencies()
        );
    }



}

