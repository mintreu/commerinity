<?php

namespace Mintreu\LaravelMoney\Services;

use Akaunting\Money\Currency;
use Akaunting\Money\Money as CoreMoney;
use Mintreu\LaravelMoney\Contracts\LaravelMoneyServiceContract;
use Mintreu\LaravelMoney\LaravelMoney;

class LaravelMoneyService implements LaravelMoneyServiceContract
{
    protected CoreMoney $laravelMoney;



    public function __construct(LaravelMoneyService|string|int|float|null $value = 0, ?string $currency = null)
    {

        $baseValue = $this->extractBaseValue($value);
        $currency = self::resolveCurrency($currency);

        if (is_null($baseValue))
        {
            $baseValue = 0.00;
        }

        // Instantiate Laravel Money
        $this->laravelMoney = new CoreMoney($baseValue, $currency, true);
    }

    public static function make(LaravelMoneyService|string|int|float|null $value = 0, ?string $currency = null):static
    {
        return new static($value, $currency);
    }

    public function sameAs(LaravelMoney|string|int $value, ?string $currency = null): bool
    {
        $givenValue = self::isMoney($value) ? $value : LaravelMoney::make($value,$currency);

        return $this->laravelMoney->equals($givenValue->laravelMoney);
    }

    public function compare(LaravelMoney|string|int $value, ?string $currency = null): int
    {
        $givenValue = self::isMoney($value) ? $value : LaravelMoney::make($value,$currency);

        return $this->laravelMoney->compare($givenValue->laravelMoney);
    }

    public function currency(): Currency
    {
        return $this->laravelMoney->getCurrency();
    }

    public function getCurrency(): Currency
    {
        return $this->currency();
    }

    public function getCurrencyCode(): string
    {
        return $this->currency()->getCurrency();
    }

    public function amount(): float|int
    {
        return $this->laravelMoney->getAmount();
    }

    public function getAmount(): float|int
    {
        return $this->laravelMoney->getAmount();
    }

    public function formatted(): string
    {
        return $this->laravelMoney->format();
    }

    public function forHuman(): string
    {
        return $this->laravelMoney->formatForHumans();
    }

    public function getValue(): float
    {
        return $this->laravelMoney->getValue();
    }

    public function get(): self
    {
        return clone $this;
    }

    public static function resolveCurrency(?string $currency = null): Currency
    {
        return new Currency($currency ?? config('laravel-money.currency'));
    }


    /**
     * @param int|float|string $value
     * @param string|null $currency
     * @param bool $converted
     * @return string
     * Converted = false, when used when you pass 10000 and want use 100
     * Usage: Money::format(value:'10000',converted: false)
     */
    public static function format(int|float|string|null $value = 0, ?string $currency = null, bool $converted = true): string
    {
        if (!$converted)
        {
            $value = self::normalizeValue($value,true);
        }
        return LaravelMoney::make($value,$currency)->formatted();
    }

    public static function isMoney($object): bool
    {
        return $object instanceof self;
    }

    private function extractBaseValue($value)
    {
        if ($value instanceof self || $value instanceof CoreMoney) {
            return $value->getValue();
        }

        return is_float($value) ? (float) $value : $value;
    }


    private static function normalizeValue(int|float|string $value = 0,bool $convert = false)
    {
        // Convert string to float or int
        if (is_string($value)) {
            if (ctype_digit($value)) {
                $value = (int) $value; // Convert numeric string to int
            } else {
                $value = (float) $value; // Convert to float if contains decimals
            }
        }

        return $convert ? $value / 100 : $value;
    }


}

