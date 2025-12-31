<?php

declare(strict_types=1);

namespace App\Observers;

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * Transaction Observer
 *
 * Handles automatic wallet balance updates when transactions complete.
 */
final class TransactionObserver
{
    /**
     * Handle transaction created event
     */
    public function created(Transaction $transaction): void
    {
        // Nothing needed here - balance updated on completion
    }

    /**
     * Handle transaction updated event
     * Updates wallet balance when transaction status changes to completed
     */
    public function updated(Transaction $transaction): void
    {
        // Only proceed if status changed to COMPLETED
        if (! $transaction->wasChanged('status')) {
            return;
        }

        // Only process when transitioning TO completed status
        if ($transaction->status !== TransactionStatusCast::COMPLETED) {
            return;
        }

        $wallet = $transaction->wallet;
        if (! $wallet) {
            return;
        }

        DB::transaction(function () use ($transaction, $wallet) {
            // Credit transaction: Add to wallet balance
            if ($transaction->type === TransactionTypeCast::CREDIT) {
                $newBalance = $wallet->balance + $transaction->amount;
                $wallet->update([
                    'balance' => $newBalance,
                    'total_credited' => $wallet->total_credited + $transaction->amount,
                ]);

                // Update balance_after using DB::table to avoid triggering observer
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['balance_after' => $newBalance]);
            }
            // Debit transaction: Just update totals (balance already deducted)
            elseif ($transaction->type === TransactionTypeCast::DEBIT) {
                $wallet->update([
                    'total_debited' => $wallet->total_debited + $transaction->amount,
                ]);

                // Update balance_after using DB::table to avoid triggering observer
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['balance_after' => $wallet->balance]);
            }
        });
    }
}
