<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Money\Money;

interface MoneyServiceInterface
{
    // Factory Methods
    public static function make(int $paisa): self;

    public static function fromPaisa(int $paisa): self;

    public static function fromRupees(float|int|string $rupees): self;

    public static function zero(): self;

    public static function fromMoney(Money $money): self;

    // Getters
    public function getAmount(): int;

    public function getAmountString(): string;

    public function getCurrency(): \Money\Currency;

    public function getCurrencyCode(): string;

    public function getMoney(): Money;

    // Formatting
    public function formatted(): string;

    public function formatForApi(): array;

    // Arithmetic
    public function plus(self|int $amount): self;

    public function minus(self|int $amount): self;

    public function times(float|int|string $multiplier): self;

    public function dividedBy(float|int|string $divisor): self;

    public function percentage(float|int $percent): self;

    // Allocation
    public function allocate(array $ratios): array;

    public function split(int $parts): array;

    // Comparison
    public function equals(self|int $amount): bool;

    public function greaterThan(self|int $amount): bool;

    public function lessThan(self|int $amount): bool;

    public function compare(self|int $amount): int;

    public function isZero(): bool;

    public function isPositive(): bool;

    public function isNegative(): bool;

    // Static Utilities
    public static function min(self ...$amounts): self;

    public static function max(self ...$amounts): self;

    public static function sum(self ...$amounts): self;

    public static function format(int $paisa): string;
}
