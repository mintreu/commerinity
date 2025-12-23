<?php

declare(strict_types=1);

namespace App\Services\UserServices;

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\WalletStatusCast;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * UserWalletService - Handles all wallet operations
 *
 * The wallet is polymorphic and can belong to:
 * - User (regular members)
 * - Admin users
 * - Staff users
 * - Any future user types (merchants, vendors, etc.)
 *
 * Transaction types supported:
 * - Credit: Money coming IN (add money, commission, refund)
 * - Debit: Money going OUT (payments, purchases)
 * - Hold: Temporary hold (pending payouts)
 * - Release: Release held funds
 * - Refund: Return of money
 * - Chargeback: Forced return
 * - Adjustment: Admin corrections
 */
final class UserWalletService
{
    /**
     * Get or create wallet for any walletable model (User, Admin, etc.)
     *
     * @param  Model  $owner  The owner model (User, Admin, Staff, etc.)
     * @param  string  $currency  Currency code (default: INR)
     */
    public function getOrCreateWallet(Model $owner, string $currency = 'INR'): Wallet
    {
        return Wallet::firstOrCreate(
            [
                'walletable_type' => get_class($owner),
                'walletable_id' => $owner->getKey(),
            ],
            [
                'currency' => $currency,
                'balance' => 0,
                'hold_balance' => 0,
                'total_credited' => 0,
                'total_debited' => 0,
                'points' => 0,
                'status' => WalletStatusCast::ACTIVE,
            ]
        );
    }

    /**
     * Get wallet for a model
     */
    public function getWallet(Model $owner): ?Wallet
    {
        return Wallet::where('walletable_type', get_class($owner))
            ->where('walletable_id', $owner->getKey())
            ->first();
    }

    /**
     * Credit money to wallet (add money, commission, refund, etc.)
     *
     * @param  string  $purpose  e.g., 'commission', 'add_money', 'refund'
     * @param  Model|null  $relatedModel  Transaction linked to (Commission, Order, etc.)
     */
    public function credit(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null,
        ?Model $relatedModel = null
    ): Transaction {
        if (! $wallet->canReceive()) {
            throw new RuntimeException('Wallet cannot receive funds');
        }

        return DB::transaction(function () use ($wallet, $amountInPaisa, $purpose, $description, $relatedModel) {
            // Update wallet balance
            $wallet->increment('balance', $amountInPaisa);
            $wallet->increment('total_credited', $amountInPaisa);

            // Create transaction record
            return Transaction::create([
                'wallet_id' => $wallet->id,
                'transactionable_type' => $relatedModel ? get_class($relatedModel) : null,
                'transactionable_id' => $relatedModel?->getKey(),
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => $purpose,
                'description' => $description,
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    /**
     * Debit money from wallet (payments, purchases, etc.)
     *
     * @param  string  $purpose  e.g., 'subscription', 'purchase', 'transfer'
     */
    public function debit(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null,
        ?Model $relatedModel = null
    ): Transaction {
        if (! $wallet->canTransact()) {
            throw new RuntimeException('Wallet cannot transact');
        }

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            throw new RuntimeException('Insufficient balance');
        }

        return DB::transaction(function () use ($wallet, $amountInPaisa, $purpose, $description, $relatedModel) {
            // Update wallet balance
            $wallet->decrement('balance', $amountInPaisa);
            $wallet->increment('total_debited', $amountInPaisa);

            // Create transaction record
            return Transaction::create([
                'wallet_id' => $wallet->id,
                'transactionable_type' => $relatedModel ? get_class($relatedModel) : null,
                'transactionable_id' => $relatedModel?->getKey(),
                'type' => TransactionTypeCast::DEBIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => $purpose,
                'description' => $description,
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    /**
     * Hold funds (for pending payouts)
     */
    public function hold(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null
    ): Transaction {
        if (! $wallet->canTransact()) {
            throw new RuntimeException('Wallet cannot transact');
        }

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            throw new RuntimeException('Insufficient balance');
        }

        return DB::transaction(function () use ($wallet, $amountInPaisa, $purpose, $description) {
            // Move from available to hold
            $wallet->decrement('balance', $amountInPaisa);
            $wallet->increment('hold_balance', $amountInPaisa);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionTypeCast::HOLD,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => $purpose,
                'description' => $description ?? 'Funds held',
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    /**
     * Release held funds back to available balance
     *
     * @param  Transaction|null  $holdTransaction  The original hold transaction
     */
    public function release(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?Transaction $holdTransaction = null
    ): Transaction {
        if ($wallet->hold_balance < $amountInPaisa) {
            throw new RuntimeException('Insufficient held funds');
        }

        return DB::transaction(function () use ($wallet, $amountInPaisa, $purpose, $holdTransaction) {
            // Move from hold back to available
            $wallet->decrement('hold_balance', $amountInPaisa);
            $wallet->increment('balance', $amountInPaisa);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionTypeCast::RELEASE,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => $purpose,
                'description' => 'Funds released',
                'parent_transaction_id' => $holdTransaction?->id,
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    /**
     * Transfer money between wallets
     *
     * @return array{debit: Transaction, credit: Transaction}
     */
    public function transfer(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $amountInPaisa,
        string $purpose = 'transfer',
        ?string $description = null
    ): array {
        if (! $fromWallet->canTransact()) {
            throw new RuntimeException('Source wallet cannot transact');
        }

        if (! $toWallet->canReceive()) {
            throw new RuntimeException('Destination wallet cannot receive');
        }

        if (! $fromWallet->hasSufficientBalance($amountInPaisa)) {
            throw new RuntimeException('Insufficient balance in source wallet');
        }

        return DB::transaction(function () use ($fromWallet, $toWallet, $amountInPaisa, $purpose, $description) {
            // Debit from source
            $debit = $this->debit(
                $fromWallet,
                $amountInPaisa,
                $purpose,
                $description ?? "Transfer to wallet #{$toWallet->uuid}"
            );

            // Credit to destination
            $credit = $this->credit(
                $toWallet,
                $amountInPaisa,
                $purpose,
                $description ?? "Transfer from wallet #{$fromWallet->uuid}"
            );

            // Link transactions
            $credit->update(['parent_transaction_id' => $debit->id]);

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    /**
     * Process refund to wallet
     */
    public function refund(
        Wallet $wallet,
        int $amountInPaisa,
        Transaction $originalTransaction,
        ?string $reason = null
    ): Transaction {
        return DB::transaction(function () use ($wallet, $amountInPaisa, $originalTransaction, $reason) {
            // Credit the wallet
            $wallet->increment('balance', $amountInPaisa);
            $wallet->increment('total_credited', $amountInPaisa);

            // Create refund transaction
            $refund = Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionTypeCast::REFUND,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => 'refund',
                'description' => $reason ?? 'Refund processed',
                'parent_transaction_id' => $originalTransaction->id,
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);

            // Update original transaction status
            $originalTransaction->update(['status' => TransactionStatusCast::REFUNDED]);

            return $refund;
        });
    }

    /**
     * Admin adjustment (credit or debit)
     *
     * @param  int  $amountInPaisa  Positive for credit, negative for debit
     * @param  int  $adminId  Admin performing the adjustment
     */
    public function adjustment(
        Wallet $wallet,
        int $amountInPaisa,
        string $reason,
        int $adminId
    ): Transaction {
        return DB::transaction(function () use ($wallet, $amountInPaisa, $reason, $adminId) {
            if ($amountInPaisa > 0) {
                // Credit adjustment
                $wallet->increment('balance', $amountInPaisa);
                $wallet->increment('total_credited', $amountInPaisa);
            } else {
                // Debit adjustment
                $absAmount = abs($amountInPaisa);
                if (! $wallet->hasSufficientBalance($absAmount)) {
                    throw new RuntimeException('Insufficient balance for debit adjustment');
                }
                $wallet->decrement('balance', $absAmount);
                $wallet->increment('total_debited', $absAmount);
            }

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionTypeCast::ADJUSTMENT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => abs($amountInPaisa),
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $amountInPaisa,
                'currency' => $wallet->currency,
                'purpose' => 'adjustment',
                'description' => "Admin adjustment: {$reason}",
                'is_verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
                'metadata' => ['admin_id' => $adminId],
            ]);
        });
    }

    /**
     * Get transaction history for a wallet
     *
     * @return Collection<Transaction>
     */
    public function getTransactionHistory(Wallet $wallet, int $limit = 20): Collection
    {
        return $wallet->transactions()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get wallet summary/stats
     */
    public function getWalletSummary(Wallet $wallet): array
    {
        return [
            'balance' => $wallet->balance,
            'hold_balance' => $wallet->hold_balance,
            'available_balance' => $wallet->available_balance,
            'total_credited' => $wallet->total_credited,
            'total_debited' => $wallet->total_debited,
            'points' => $wallet->points,
            'currency' => $wallet->currency,
            'status' => $wallet->status->value,
            'has_pin' => $wallet->hasPin(),
        ];
    }

    /**
     * Add points to wallet
     */
    public function addPoints(Wallet $wallet, int $points): void
    {
        $wallet->increment('points', $points);
    }

    /**
     * Deduct points from wallet
     */
    public function deductPoints(Wallet $wallet, int $points): void
    {
        if ($wallet->points < $points) {
            throw new RuntimeException('Insufficient points');
        }

        $wallet->decrement('points', $points);
    }
}
