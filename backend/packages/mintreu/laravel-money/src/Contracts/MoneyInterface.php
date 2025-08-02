<?php

namespace Mintreu\LaravelMoney\Contracts;

use Mintreu\LaravelMoney\LaravelMoney;

interface MoneyInterface
{
    public function addOnce(LaravelMoney|int|float $addend): self;

    public function add(LaravelMoney|int|float $addend): self;

    public function subOnce(LaravelMoney|int|float $subtrahend): self;

    public function subtract(LaravelMoney|int|float $subtrahend): self;

    public function multiplyOnce(int|float $multiplier): self;

    public function multiply(int|float $multiplier): self;

    public function divideOnce(int|float $divisor): self;

    public function divide(int|float $divisor): self;
}
