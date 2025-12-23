<?php

declare(strict_types=1);

namespace App\Jobs\Wallet;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Models\BeneficiaryAccount;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;
use App\Services\Payment\Providers\CashfreePayoutProvider;
use App\Services\Payment\Providers\NativePayoutProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessPayoutJob - Handle wallet withdrawal to bank/UPI
 *
 * This job processes payout requests using the configured payment provider.
 * It handles:
 * - Initiating payout via Cashfree/Razorpay/Native
 * - Updating transaction status
 * - Releasing or refunding held funds
 */
final class ProcessPayoutJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly int $transactionId,
        public readonly int $beneficiaryAccountId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = Transaction::find($this->transactionId);
        $beneficiary = BeneficiaryAccount::find($this->beneficiaryAccountId);

        if (! $transaction || ! $beneficiary) {
            Log::error('Payout job: Transaction or beneficiary not found', [
                'transaction_id' => $this->transactionId,
                'beneficiary_id' => $this->beneficiaryAccountId,
            ]);

            return;
        }

        // Only process pending/on_hold transactions
        $validStatuses = [TransactionStatusCast::PENDING, TransactionStatusCast::ON_HOLD];
        if (! in_array($transaction->status, $validStatuses, true)) {
            Log::info('Payout job: Transaction already processed', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status->value,
            ]);

            return;
        }

        $wallet = $transaction->wallet;
        if (! $wallet) {
            Log::error('Payout job: Wallet not found for transaction', [
                'transaction_id' => $transaction->id,
            ]);
            $this->failTransaction($transaction, 'Wallet not found');

            return;
        }

        // Get the appropriate payout provider
        $provider = $this->getPayoutProvider();

        if (! $provider->isAvailable()) {
            Log::warning('Payout job: Provider not available, using native', [
                'provider' => $provider->getSlug(),
            ]);
            $provider = new NativePayoutProvider;
        }

        // Create payout request
        $payoutRequest = new PayoutRequest(
            amountInPaisa: $transaction->amount,
            currency: $wallet->currency,
            method: $beneficiary->isUpi() ? PaymentMethodCast::UPI : PaymentMethodCast::BANK_TRANSFER,
            userId: $wallet->walletable_id,
            walletId: $wallet->id,
            beneficiaryAccountId: $beneficiary->id,
            transactionId: $transaction->reference_number,
            purpose: 'withdrawal',
            description: "Withdrawal to {$beneficiary->display_name}",
            metadata: [
                'holder_name' => $beneficiary->holder_name,
                'account_type' => $beneficiary->type->value,
            ],
        );

        // Initiate payout
        $response = $provider->initiate($payoutRequest);

        // Handle response
        $this->processPayoutResponse($transaction, $wallet, $response, $provider->getSlug());
    }

    /**
     * Get the configured payout provider
     */
    private function getPayoutProvider(): PayoutProviderInterface
    {
        // Try Cashfree first (primary payout provider for India)
        $cashfree = new CashfreePayoutProvider;
        if ($cashfree->isAvailable()) {
            return $cashfree;
        }

        // Fallback to native (manual processing)
        return new NativePayoutProvider;
    }

    /**
     * Process the payout response
     */
    private function processPayoutResponse(
        Transaction $transaction,
        Wallet $wallet,
        PayoutResponse $response,
        string $providerSlug
    ): void {
        DB::transaction(function () use ($transaction, $wallet, $response, $providerSlug) {
            $metadata = array_merge($transaction->metadata ?? [], [
                'payout_provider' => $providerSlug,
                'payout_response' => $response->toArray(),
                'provider_payout_id' => $response->providerPayoutId,
                'utr_number' => $response->utrNumber,
            ]);

            if ($response->success) {
                if ($response->isCompleted()) {
                    // Payout completed - finalize the transaction
                    $transaction->update([
                        'status' => TransactionStatusCast::COMPLETED,
                        'metadata' => $metadata,
                        'completed_at' => now(),
                    ]);

                    // Release the hold and debit the amount
                    $wallet->decrement('hold_balance', $transaction->amount);

                    Log::info('Payout completed', [
                        'transaction_id' => $transaction->id,
                        'provider' => $providerSlug,
                        'utr' => $response->utrNumber,
                    ]);
                } else {
                    // Payout processing - keep transaction on hold
                    $transaction->update([
                        'status' => TransactionStatusCast::PROCESSING,
                        'metadata' => $metadata,
                    ]);

                    Log::info('Payout processing', [
                        'transaction_id' => $transaction->id,
                        'provider' => $providerSlug,
                        'provider_id' => $response->providerPayoutId,
                    ]);
                }
            } else {
                // Payout failed - refund the held amount
                $this->failTransaction($transaction, $response->message ?? 'Payout failed');
            }
        });
    }

    /**
     * Mark transaction as failed and refund held amount
     */
    private function failTransaction(Transaction $transaction, string $reason): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $transaction->update([
                'status' => TransactionStatusCast::FAILED,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'failure_reason' => $reason,
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            // Refund held amount back to available balance
            $wallet = $transaction->wallet;
            if ($wallet && $transaction->amount > 0) {
                $wallet->decrement('hold_balance', $transaction->amount);
                $wallet->increment('balance', $transaction->amount);

                Log::info('Payout failed - funds refunded', [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'reason' => $reason,
                ]);
            }
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Payout job failed permanently', [
            'transaction_id' => $this->transactionId,
            'beneficiary_id' => $this->beneficiaryAccountId,
            'exception' => $exception?->getMessage(),
        ]);

        // Mark transaction as failed
        $transaction = Transaction::find($this->transactionId);
        if ($transaction && $transaction->status !== TransactionStatusCast::FAILED) {
            $this->failTransaction($transaction, 'Payout job failed: '.($exception?->getMessage() ?? 'Unknown error'));
        }
    }
}
