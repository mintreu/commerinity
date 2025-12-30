<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * HasWallet Trait
 *
 * Add to User model to enable wallet functionality.
 * Provides easy access to the user's wallet and balance.
 *
 * Usage:
 *   use HasWallet;
 *   // $user->wallet returns MorphOne relationship
 *   // $user->getWalletBalance() returns balance
 *   // $user->hasWallet() checks if wallet exists
 */
trait HasWallet
{
    /**
     * Get the wallet associated with this model
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    /**
     * Get the wallet balance in paise
     */
    public function getWalletBalance(): int
    {
        return $this->wallet?->balance ?? 0;
    }

    /**
     * Get the wallet balance in rupees (for display)
     */
    public function getWalletBalanceInRupees(): float
    {
        return $this->getWalletBalance() / 100;
    }

    /**
     * Check if the model has a wallet
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
    }

    /**
     * Get wallet or throw exception
     */
    public function getWallet(): Wallet
    {
        return $this->wallet()->firstOrFail();
    }

    /**
     * Get wallet UUID or null
     */
    public function getWalletUuid(): ?string
    {
        return $this->wallet?->uuid;
    }

    /**
     * Check if wallet can transact
     */
    public function walletCanTransact(): bool
    {
        return $this->wallet?->canTransact() ?? false;
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientWalletBalance(int $amountInPaisa): bool
    {
        return $this->wallet?->hasSufficientBalance($amountInPaisa) ?? false;
    }
}
