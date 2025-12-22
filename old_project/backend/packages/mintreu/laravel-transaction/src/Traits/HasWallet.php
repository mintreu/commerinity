<?php

namespace Mintreu\LaravelTransaction\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mintreu\LaravelTransaction\Models\Wallet;

trait HasWallet
{

    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }


    /**
     * Get the wallet balance.
     */
    public function getWalletBalance(): float
    {
        return $this->wallet?->balance ?? 0.00;
    }

    /**
     * Check if the model has a wallet.
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
    }



}
