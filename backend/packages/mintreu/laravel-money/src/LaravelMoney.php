<?php

namespace Mintreu\LaravelMoney;

use Akaunting\Money\Currency;
use Mintreu\LaravelMoney\Services\LaravelMoneyService;


class LaravelMoney extends LaravelMoneyService
{
    public static function defaultCurrency(): string
    {
        return config('laravel-money.currency');
    }

    public static function getCurrencySymbol(?string $currency = null): string
    {
        $currency = $currency ?? self::defaultCurrency();

        return (new Currency($currency))->getSymbol();
    }

    public function addOnce(LaravelMoney|int|float $addend): self
    {
        $result = $this->laravelMoney->add(self::isMoney($addend) ? $addend->get()->getAmount() : LaravelMoney::make($addend)->getAmount());

        return LaravelMoney::make($result);
    }

    public function add(LaravelMoney|int|float $addend): self
    {
        $this->laravelMoney = $this->laravelMoney->add(self::isMoney($addend) ? $addend->getAmount() : LaravelMoney::make($addend)->getAmount());

        return $this;
    }

    public function subOnce(LaravelMoney|int|float $subtrahend): self
    {
        $result = $this->laravelMoney->subtract(self::isMoney($subtrahend) ? $subtrahend->getAmount() : LaravelMoney::make($subtrahend)->getAmount());
        return LaravelMoney::make($result);
    }

    public function subtract(LaravelMoney|int|float $subtrahend): self
    {
        $this->laravelMoney = $this->laravelMoney->subtract(self::isMoney($subtrahend) ? $subtrahend->getAmount() : LaravelMoney::make($subtrahend)->getAmount());

        return $this;
    }

    public function multiplyOnce(int|float $multiplier): self
    {
        $result = $this->laravelMoney->multiply($multiplier);
        return LaravelMoney::make($result);
    }

    public function multiply(int|float $multiplier): self
    {
        $this->laravelMoney = $this->laravelMoney->multiply($multiplier);

        return $this;
    }

    public function divideOnce(int|float $divisor): self
    {
        $result = $this->laravelMoney->divide($divisor);
        return LaravelMoney::make($result);
    }

    public function divide(int|float $divisor): self
    {
        $this->laravelMoney = $this->laravelMoney->divide($divisor);

        return $this;
    }

    public function greaterThan(LaravelMoney $money)
    {
        return $this->laravelMoney->greaterThan($money->laravelMoney);
    }

    public function greaterThanOrEqual(LaravelMoney $money)
    {
        return $this->laravelMoney->greaterThanOrEqual($money->laravelMoney);
    }

    public function lessThan(LaravelMoney $money)
    {
        return $this->laravelMoney->lessThan($money->laravelMoney);
    }
}

