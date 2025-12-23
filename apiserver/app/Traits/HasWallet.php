<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait for models that have a wallet (User, Admin, etc.)
 *
 * Provides wallet relationship and common wallet-related functionality.
 */
trait HasWallet
{
    /**
     * Get the model's wallet.
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    /**
     * Get or create wallet for this model.
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?? $this->createWallet();
    }

    /**
     * Create a new wallet for this model.
     */
    public function createWallet(array $attributes = []): Wallet
    {
        return $this->wallet()->create(array_merge([
            'balance' => 0,
            'hold_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'points' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ], $attributes));
    }

    /**
     * Check if model has a wallet.
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
    }

    /**
     * Check if model has a wallet with PIN set.
     */
    public function hasWalletPin(): bool
    {
        return $this->wallet?->hasPin() ?? false;
    }

    /**
     * Get wallet balance in paisa.
     */
    public function getWalletBalance(): int
    {
        return $this->wallet?->balance ?? 0;
    }

    /**
     * Get wallet available balance (balance - hold).
     */
    public function getWalletAvailableBalance(): int
    {
        return $this->wallet?->available_balance ?? 0;
    }

    /**
     * Get formatted wallet balance.
     */
    public function getFormattedWalletBalance(): string
    {
        return $this->wallet?->formatted_balance ?? '₹0.00';
    }

    /**
     * Check if wallet has sufficient balance.
     */
    public function hasSufficientBalance(int $amountInPaisa): bool
    {
        return $this->wallet?->hasSufficientBalance($amountInPaisa) ?? false;
    }

    /**
     * Check if wallet can transact.
     */
    public function canTransactFromWallet(): bool
    {
        return $this->wallet?->canTransact() ?? false;
    }

    /**
     * Get wallet transactions.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Transaction>
     */
    public function getWalletTransactions(int $limit = 10)
    {
        if (! $this->wallet) {
            return collect();
        }

        return $this->wallet->transactions()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent wallet activity summary.
     *
     * @return array{credits: int, debits: int, net: int, count: int}
     */
    public function getWalletActivitySummary(int $days = 30): array
    {
        if (! $this->wallet) {
            return ['credits' => 0, 'debits' => 0, 'net' => 0, 'count' => 0];
        }

        $transactions = $this->wallet->transactions()
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->get();

        $credits = $transactions->where('type', 'credit')->sum('amount');
        $debits = $transactions->where('type', 'debit')->sum('amount');

        return [
            'credits' => $credits,
            'debits' => $debits,
            'net' => $credits - $debits,
            'count' => $transactions->count(),
        ];
    }
}
