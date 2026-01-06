<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payout\Providers;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\BeneficiaryAccount;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\IntegrationServices\Payout\Contracts\PayoutProviderInterface;
use App\Services\IntegrationServices\Payout\DTOs\PayoutRequest;
use App\Services\IntegrationServices\Payout\DTOs\PayoutResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NativePayoutProvider - Handles manual bank/UPI payouts
 *
 * This is a "native" payout provider for manual processing.
 * Admin confirms when funds are actually transferred.
 * For automated payouts, use CashfreePayoutProvider (future).
 */
final class NativePayoutProvider implements PayoutProviderInterface
{
    public function getSlug(): string
    {
        return 'native';
    }

    public function getName(): string
    {
        return 'Manual Payout';
    }

    public function isAvailable(): bool
    {
        return true; // Always available
    }

    public function initiate(PayoutRequest $request): PayoutResponse
    {
        $wallet = Wallet::find($request->walletId);

        if (! $wallet) {
            return PayoutResponse::failed('Wallet not found');
        }

        if (! $wallet->canTransact()) {
            return PayoutResponse::failed('Wallet is not active');
        }

        if (! $wallet->hasSufficientBalance($request->amountInPaisa)) {
            return PayoutResponse::failed('Insufficient wallet balance');
        }

        $beneficiary = BeneficiaryAccount::find($request->beneficiaryAccountId);

        if (! $beneficiary) {
            return PayoutResponse::failed('Beneficiary account not found');
        }

        if (! $beneficiary->canReceivePayout()) {
            return PayoutResponse::failed('Beneficiary account is not verified');
        }

        try {
            $transaction = DB::transaction(function () use ($wallet, $request, $beneficiary) {
                // Hold the amount (debit from available, add to hold)
                $wallet->decrement('balance', $request->amountInPaisa);
                $wallet->increment('hold_balance', $request->amountInPaisa);

                // Create pending payout transaction
                return Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => TransactionTypeCast::DEBIT,
                    'status' => TransactionStatusCast::PENDING,
                    'amount' => $request->amountInPaisa,
                    'fee' => 0,
                    'tax' => 0,
                    'net_amount' => $request->amountInPaisa,
                    'currency' => $request->currency,
                    'payment_method' => $request->method,
                    'description' => $request->description ?? 'Withdrawal to '.$beneficiary->type->getLabel(),
                    'purpose' => $request->purpose ?? 'withdrawal',
                    'verified' => false,
                    'balance_after' => $wallet->balance,
                    'metadata' => array_merge($request->metadata, [
                        'beneficiary_id' => $beneficiary->id,
                        'beneficiary_type' => $beneficiary->type->value,
                        'account_number' => $this->maskAccountNumber($beneficiary->account_number),
                        'holder_name' => $beneficiary->holder_name,
                    ]),
                ]);
            });

            return PayoutResponse::pending(
                message: 'Payout request submitted. Processing within 1-3 business days.',
                transactionId: $transaction->uuid,
            );
        } catch (\Exception $e) {
            return PayoutResponse::failed('Failed to initiate payout: '.$e->getMessage());
        }
    }

    public function checkStatus(string $payoutId): PayoutResponse
    {
        $transaction = Transaction::where('uuid', $payoutId)->first();

        if (! $transaction) {
            return PayoutResponse::failed('Payout not found');
        }

        $utrNumber = $transaction->metadata['utr_number'] ?? null;

        return new PayoutResponse(
            success: $transaction->status === TransactionStatusCast::COMPLETED,
            status: $transaction->status->value,
            message: $transaction->status->getLabel(),
            transactionId: $transaction->uuid,
            utrNumber: $utrNumber,
        );
    }

    public function getSupportedMethods(): array
    {
        return [
            PaymentMethodCast::PAYOUT_BANK->value,
            PaymentMethodCast::PAYOUT_UPI->value,
        ];
    }

    /**
     * Confirm payout completion (admin use)
     */
    public function confirmPayout(string $transactionId, ?string $utrNumber = null): PayoutResponse
    {
        $transaction = Transaction::where('uuid', $transactionId)->first();

        if (! $transaction) {
            return PayoutResponse::failed('Payout not found');
        }

        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return PayoutResponse::failed('Payout is not pending');
        }

        try {
            DB::transaction(function () use ($transaction, $utrNumber) {
                $wallet = $transaction->wallet;

                // Release hold and finalize debit
                $wallet->decrement('hold_balance', $transaction->amount);
                $wallet->increment('total_debited', $transaction->amount);

                // Update transaction
                $metadata = $transaction->metadata ?? [];
                if ($utrNumber) {
                    $metadata['utr_number'] = $utrNumber;
                }

                $transaction->update([
                    'status' => TransactionStatusCast::COMPLETED,
                    'verified' => true,
                    'verified_at' => now(),
                    'metadata' => $metadata,
                ]);
            });

            return PayoutResponse::success(
                message: 'Payout completed',
                transactionId: $transaction->uuid,
                utrNumber: $utrNumber,
            );
        } catch (\Exception $e) {
            return PayoutResponse::failed('Failed to confirm payout: '.$e->getMessage());
        }
    }

    /**
     * Cancel/reject a pending payout (admin use)
     */
    public function cancelPayout(string $transactionId, ?string $reason = null): PayoutResponse
    {
        $transaction = Transaction::where('uuid', $transactionId)->first();

        if (! $transaction) {
            return PayoutResponse::failed('Payout not found');
        }

        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return PayoutResponse::failed('Payout is not pending');
        }

        try {
            DB::transaction(function () use ($transaction, $reason) {
                $wallet = $transaction->wallet;

                // Release hold back to available balance
                $wallet->decrement('hold_balance', $transaction->amount);
                $wallet->increment('balance', $transaction->amount);

                // Update transaction
                $transaction->update([
                    'status' => TransactionStatusCast::CANCELLED,
                    'notes' => $reason ?? 'Payout cancelled',
                ]);
            });

            return new PayoutResponse(
                success: true,
                status: PayoutResponse::STATUS_CANCELLED,
                message: 'Payout cancelled. Amount returned to wallet.',
                transactionId: $transaction->uuid,
            );
        } catch (\Exception $e) {
            return PayoutResponse::failed('Failed to cancel payout: '.$e->getMessage());
        }
    }

    // ========================================
    // Beneficiary Operations
    // ========================================

    /**
     * Create beneficiary account (native = just store locally, auto-verify)
     *
     * @param  BeneficiaryAccount $beneficiary  The beneficiary account to register
     * @param  ?Integration  $integration  Optional integration override (unused for native)
     * @return array{success: bool, beneficiary_id?: string, message?: string}
     */
    public function createBeneficiary(BeneficiaryAccount $beneficiary, ?Integration $integration = null): array
    {
        try {
            // For native provider, just mark as verified (no external API)
            $beneficiary->update([
                'status' => BeneficiaryStatusCast::VERIFIED,
                'provider_beneficiary_id' => 'NATIVE-' . $beneficiary->id,
            ]);

            Log::info('Native beneficiary verified', [
                'beneficiary_id' => $beneficiary->id,
                'wallet_id' => $beneficiary->wallet_id,
            ]);

            return [
                'success' => true,
                'beneficiary_id' => (string) $beneficiary->id,
                'message' => 'Beneficiary account verified successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Native beneficiary verification failed', [
                'error' => $e->getMessage(),
                'beneficiary_id' => $beneficiary->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create beneficiary: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Update beneficiary account
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message?: string}
     */
    public function updateBeneficiary(BeneficiaryAccount $beneficiary, array $data): array
    {
        try {
            $updateData = array_filter([
                'holder_name' => $data['holder_name'] ?? $data['account_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'ifsc_code' => isset($data['ifsc']) ? strtoupper($data['ifsc']) : (isset($data['ifsc_code']) ? strtoupper($data['ifsc_code']) : null),
                'bank_name' => $data['bank_name'] ?? null,
                'bank_branch' => $data['bank_branch'] ?? null,
                'upi_id' => $data['upi_id'] ?? $data['upi_handle'] ?? null,
            ], fn ($v) => $v !== null);

            $beneficiary->update($updateData);

            Log::info('Native beneficiary updated', ['beneficiary_id' => $beneficiary->id]);

            return [
                'success' => true,
                'message' => 'Beneficiary account updated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Native beneficiary update failed', [
                'error' => $e->getMessage(),
                'beneficiary_id' => $beneficiary->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update beneficiary: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete beneficiary account
     *
     * @return array{success: bool, message?: string}
     */
    public function deleteBeneficiary(BeneficiaryAccount $beneficiary): array
    {
        try {
            $wasDefault = $beneficiary->is_default;
            $walletId = $beneficiary->wallet_id;

            $beneficiary->delete();

            // Assign new default if needed
            if ($wasDefault) {
                BeneficiaryAccount::where('wallet_id', $walletId)
                    ->first()
                    ?->update(['is_default' => true]);
            }

            Log::info('Native beneficiary deleted', ['beneficiary_id' => $beneficiary->id]);

            return [
                'success' => true,
                'message' => 'Beneficiary account deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Native beneficiary deletion failed', [
                'error' => $e->getMessage(),
                'beneficiary_id' => $beneficiary->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete beneficiary: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get beneficiary details
     *
     * @return array{success: bool, data?: array<string, mixed>, message?: string}
     */
    public function getBeneficiary(BeneficiaryAccount $beneficiary): array
    {
        return [
            'success' => true,
            'data' => [
                'id' => $beneficiary->id,
                'uuid' => $beneficiary->uuid,
                'type' => $beneficiary->type->value,
                'holder_name' => $beneficiary->holder_name,
                'account_number' => $this->maskAccountNumber($beneficiary->account_number),
                'ifsc_code' => $beneficiary->ifsc_code,
                'bank_name' => $beneficiary->bank_name,
                'upi_id' => $beneficiary->upi_id,
                'status' => $beneficiary->status->value,
                'is_default' => $beneficiary->is_default,
            ],
        ];
    }

    /**
     * Mask account number for display
     */
    private function maskAccountNumber(?string $accountNumber): ?string
    {
        if (! $accountNumber || strlen($accountNumber) < 4) {
            return $accountNumber;
        }

        return str_repeat('*', strlen($accountNumber) - 4).substr($accountNumber, -4);
    }
}
