<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput;
use App\Services\MoneyService;

final class MoneyInput extends TextInput
{
    protected string|Closure|null $currency = 'INR';
    protected float|Closure|null $minAmount = 0.0;
    protected float|Closure|null $stepAmount = 0.01;
    protected bool|Closure $autoRequired = false;
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
            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) ((float) $state * $this->getStorageMultiplier()) : null)
            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state / $this->getStorageMultiplier(), 2, '.', '') : null)
            ->hint(fn ($state) => $state !== null ? MoneyService::format((int) $state) : null);

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
        return $this->evaluate($this->currency) ?? 'INR';
    }

    public function minAmount(float|Closure|null $amount): static
    {
        $this->minAmount = $amount;
        return $this;
    }

    public function getMinAmount(): float
    {
        return $this->evaluate($this->minAmount) ?? 0.0;
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
        return $this->evaluate($this->autoRequired) ?? false;
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
}
