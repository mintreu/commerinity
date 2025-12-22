<?php

namespace Mintreu\LaravelMoney\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput;
use Mintreu\LaravelMoney\LaravelMoney;

class MoneyInput extends TextInput
{
    protected string|Closure|null $currency = '₹';
    protected float|Closure|null $minAmount = 0.01;
    protected float|Closure|null $stepAmount = 0.01;
    protected bool|Closure $autoRequired = true;
    protected int|Closure $storageMultiplier = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numeric()
            ->step($this->getStepAmount())
            ->minValue($this->getMinAmount())
            ->lazy()
            ->prefix($this->getCurrency())
            ->inputMode('decimal')
            ->dehydrateStateUsing(fn ($state) => $state ? (int) ($state * $this->getStorageMultiplier()) : null)
            //->formatStateUsing(fn ($state) => $state ? number_format($state / $this->getStorageMultiplier(), 2, '.', '') : null)
            ->hint(fn($state) => LaravelMoney::format($state))
//            ->hint(function ($state){
//                dump($state,LaravelMoney::format($state));
//            })
        ;

        // Auto-apply required if enabled
        if ($this->getAutoRequired()) {
            $this->required();
        }
    }

    public function currency(string|Closure|null $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->evaluate($this->currency) ?? '₹';
    }

    public function minAmount(float|Closure|null $amount): static
    {
        $this->minAmount = $amount;
        return $this;
    }

    public function getMinAmount(): float
    {
        return $this->evaluate($this->minAmount) ?? 0.01;
    }

    public function stepAmount(float|Closure|null $step): static
    {
        $this->stepAmount = $step;
        return $this;
    }

    public function getStepAmount(): float
    {
        return $this->evaluate($this->stepAmount) ?? 0.01;
    }

    public function autoRequired(bool|Closure $condition = true): static
    {
        $this->autoRequired = $condition;
        return $this;
    }

    public function getAutoRequired(): bool
    {
        return $this->evaluate($this->autoRequired) ?? true;
    }

    public function storageMultiplier(int|Closure $multiplier): static
    {
        $this->storageMultiplier = $multiplier;
        return $this;
    }

    public function getStorageMultiplier(): int
    {
        return $this->evaluate($this->storageMultiplier) ?? 100;
    }

    // Corrected convenience methods that accept name parameter
    public static function usd(string $name): static
    {
        return static::make($name)->currency('$');
    }

    public static function eur(string $name): static
    {
        return static::make($name)->currency('€');
    }

    public static function inr(string $name): static
    {
        return static::make($name)->currency('₹');
    }
}
