<?php

declare(strict_types=1);

namespace App\Jobs\Wallet;

use App\Casts\TransactionStatusCast;
use App\Models\Transaction;
use App\Services\IntegrationServices\Payout\DTOs\PayoutResponse;
use App\Services\IntegrationServices\Payout\Providers\CashfreePayoutProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CheckPayoutStatusJob - Check status of pending payouts
 *
 * This job is scheduled to run periodically to check and update
 * the status of processing payout transactions.
 */
final class CheckPayoutStatusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly ?int $transactionId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Transaction::query()
            ->where('purpose', 'withdrawal')
            ->where('status', TransactionStatusCast::PROCESSING)
            ->whereNotNull('metadata->provider_payout_id')
            ->where('created_at', '>=', now()->subDays(7));

        // If specific transaction ID provided, check only that one
        if ($this->transactionId) {
            $query->where('id', $this->transactionId);
        }

        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            Log::info('No pending payouts to check');

            return;
        }

        Log::info('Checking payout status', ['count' => $transactions->count()]);

        $provider = new CashfreePayoutProvider;

        foreach ($transactions as $transaction) {
            $this->checkTransaction($transaction, $provider);
        }
    }

    /**
     * Check status of a single transaction
     */
    private function checkTransaction(Transaction $transaction, CashfreePayoutProvider $provider): void
    {
        $providerPayoutId = $transaction->metadata['provider_payout_id'] ?? null;

        if (! $providerPayoutId) {
            // Use reference number as fallback
            $providerPayoutId = $transaction->reference_number;
        }

        if (! $provider->isAvailable()) {
            Log::warning('Payout provider not available for status check', [
                'transaction_id' => $transaction->id,
            ]);

            return;
        }

        try {
            $response = $provider->checkStatus($providerPayoutId);

            $this->updateTransaction($transaction, $response);
        } catch (\Exception $e) {
            Log::error('Payout status check failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update transaction based on provider response
     */
    private function updateTransaction(Transaction $transaction, PayoutResponse $response): void
    {
        $metadata = array_merge($transaction->metadata ?? [], [
            'last_status_check' => now()->toIso8601String(),
            'provider_status' => $response->status,
            'utr_number' => $response->utrNumber ?? ($transaction->metadata['utr_number'] ?? null),
        ]);

        DB::transaction(function () use ($transaction, $response, $metadata) {
            $wallet = $transaction->wallet;

            if ($response->isCompleted()) {
                // Payout completed
                $transaction->update([
                    'status' => TransactionStatusCast::COMPLETED,
                    'metadata' => $metadata,
                    'completed_at' => now(),
                ]);

                // Release hold
                if ($wallet) {
                    $wallet->decrement('hold_balance', $transaction->amount);
                }

                Log::info('Payout completed via status check', [
                    'transaction_id' => $transaction->id,
                    'utr' => $response->utrNumber,
                ]);
            } elseif ($response->status === PayoutResponse::STATUS_FAILED) {
                // Payout failed - refund
                $transaction->update([
                    'status' => TransactionStatusCast::FAILED,
                    'metadata' => array_merge($metadata, [
                        'failure_reason' => $response->message ?? 'Provider reported failure',
                    ]),
                ]);

                // Refund held amount
                if ($wallet && $transaction->amount > 0) {
                    $wallet->decrement('hold_balance', $transaction->amount);
                    $wallet->increment('balance', $transaction->amount);
                }

                Log::info('Payout failed via status check', [
                    'transaction_id' => $transaction->id,
                    'reason' => $response->message,
                ]);
            } elseif ($response->status === PayoutResponse::STATUS_REVERSED) {
                // Payout reversed - refund
                $transaction->update([
                    'status' => TransactionStatusCast::REVERSED,
                    'metadata' => array_merge($metadata, [
                        'reversed_at' => now()->toIso8601String(),
                    ]),
                ]);

                // Refund held amount
                if ($wallet && $transaction->amount > 0) {
                    $wallet->decrement('hold_balance', $transaction->amount);
                    $wallet->increment('balance', $transaction->amount);
                }

                Log::info('Payout reversed via status check', [
                    'transaction_id' => $transaction->id,
                ]);
            } else {
                // Still processing - just update metadata
                $transaction->update(['metadata' => $metadata]);
            }
        });
    }
}
