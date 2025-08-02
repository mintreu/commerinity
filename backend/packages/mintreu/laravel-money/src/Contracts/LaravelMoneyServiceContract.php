<?php

namespace Mintreu\LaravelMoney\Contracts;

use Akaunting\Money\Currency;
use Mintreu\LaravelMoney\LaravelMoney;

interface LaravelMoneyServiceContract
{
    public function sameAs(LaravelMoney|string|int $value, ?string $currency = null): bool;

    public function compare(LaravelMoney|string|int $value, ?string $currency = null): int;

    public function currency(): Currency;

    public function getCurrency(): Currency;

    public function getCurrencyCode(): string;

    public function amount(): float|int;

    public function getAmount(): float|int;

    public function formatted(): string;

    public function forHuman(): string;

    public function getValue(): float;

    public function get():self;

    public static function resolveCurrency(?string $currency = null): Currency;

    public static function format(int|float|string $value, ?string $currency = null): string;

    public static function isMoney($object): bool;
}
