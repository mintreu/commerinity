<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment\Providers;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use Illuminate\Support\Facades\DB;

/**
 * NativePaymentProvider - Handles wallet, cash, COD, and bank transfer payments
 *
 * These are "native" payment methods that don't require third-party integration.
 * - Wallet: Immediate debit from user's wallet balance
 * - Cash: Manual confirmation (for in-person payments)
 * - COD: Cash on delivery (confirmed after delivery)
 * - Bank Transfer: Manual confirmation after receiving funds
 */
final class NativePaymentProvider implements PaymentProviderInterface
{
    public function getSlug(): string
    {
        return 'native';
    }

    public function getName(): string
    {
        return 'Native Payments';
    }

    public function isAvailable(): bool
    {
        return true; // Always available
    }

    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        return match ($request->method) {
            PaymentMethodCast::WALLET => $this->processWalletPayment($request),
            PaymentMethodCast::CASH,
            PaymentMethodCast::COD,
            PaymentMethodCast::BANK_TRANSFER => $this->createPendingPayment($request),
            default => PaymentResponse::failed('Unsupported payment method for native provider'),
        };
    }

    public function verify(PaymentVerifyRequest $request): PaymentResponse
    {
        $transaction = Transaction::where('uuid', $request->orderId)->first();

        if (! $transaction) {
            return PaymentResponse::failed('Transaction not found');
        }

        // For native payments, verification is done manually
        // Just return current status
        return new PaymentResponse(
            success: $transaction->status === TransactionStatusCast::COMPLETED,
            status: $transaction->status->value,
            message: $transaction->status->getLabel(),
            transactionId: $transaction->uuid,
        );
    }

    public function refund(string $transactionId, int $amountInPaisa, ?string $reason = null): PaymentResponse
    {
        $transaction = Transaction::where('uuid', $transactionId)->first();

        if (! $transaction) {
            return PaymentResponse::failed('Transaction not found');
        }

        if (! $transaction->canBeRefunded()) {
            return PaymentResponse::failed('Transaction cannot be refunded');
        }

        try {
            $refundTransaction = DB::transaction(function () use ($transaction, $amountInPaisa, $reason) {
                $wallet = $transaction->wallet;

                // Create refund transaction
                $refund = Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => TransactionTypeCast::REFUND,
                    'status' => TransactionStatusCast::COMPLETED,
                    'amount' => $amountInPaisa,
                    'fee' => 0,
                    'tax' => 0,
                    'net_amount' => $amountInPaisa,
                    'currency' => $transaction->currency,
                    'payment_method' => $transaction->payment_method,
                    'parent_transaction_id' => $transaction->id,
                    'description' => 'Refund: '.($reason ?? 'User requested'),
                    'purpose' => 'refund',
                    'verified' => true,
                    'verified_at' => now(),
                ]);

                // Credit wallet
                $wallet->increment('balance', $amountInPaisa);
                $wallet->increment('total_credited', $amountInPaisa);

                // Update refund with balance after
                $refund->update(['balance_after' => $wallet->balance]);

                // Mark original as refunded
                $transaction->update(['status' => TransactionStatusCast::REFUNDED]);

                return $refund;
            });

            return PaymentResponse::success(
                status: PaymentResponse::STATUS_REFUNDED,
                message: 'Refund processed successfully',
                transactionId: $refundTransaction->uuid,
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed('Refund failed: '.$e->getMessage());
        }
    }

    public function getSupportedMethods(): array
    {
        return [
            PaymentMethodCast::WALLET->value,
            PaymentMethodCast::CASH->value,
            PaymentMethodCast::COD->value,
            PaymentMethodCast::BANK_TRANSFER->value,
        ];
    }

    /**
     * Process immediate wallet payment
     */
    private function processWalletPayment(PaymentInitiateRequest $request): PaymentResponse
    {
        $wallet = Wallet::find($request->walletId);

        if (! $wallet) {
            return PaymentResponse::failed('Wallet not found');
        }

        if (! $wallet->canTransact()) {
            return PaymentResponse::failed('Wallet is not active');
        }

        if (! $wallet->hasSufficientBalance($request->amountInPaisa)) {
            return PaymentResponse::failed('Insufficient wallet balance');
        }

        try {
            $transaction = DB::transaction(function () use ($wallet, $request) {
                // Debit wallet
                $wallet->decrement('balance', $request->amountInPaisa);
                $wallet->increment('total_debited', $request->amountInPaisa);

                // Create completed transaction
                return Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => TransactionTypeCast::DEBIT,
                    'status' => TransactionStatusCast::COMPLETED,
                    'amount' => $request->amountInPaisa,
                    'fee' => 0,
                    'tax' => 0,
                    'net_amount' => $request->amountInPaisa,
                    'currency' => $request->currency,
                    'payment_method' => PaymentMethodCast::WALLET,
                    'description' => $request->description,
                    'purpose' => $request->purpose,
                    'verified' => true,
                    'verified_at' => now(),
                    'balance_after' => $wallet->balance,
                    'metadata' => $request->metadata,
                ]);
            });

            return PaymentResponse::success(
                message: 'Payment completed',
                transactionId: $transaction->uuid,
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed('Payment failed: '.$e->getMessage());
        }
    }

    /**
     * Create pending payment for manual confirmation
     */
    private function createPendingPayment(PaymentInitiateRequest $request): PaymentResponse
    {
        try {
            $transaction = Transaction::create([
                'wallet_id' => $request->walletId,
                'type' => TransactionTypeCast::DEBIT,
                'status' => TransactionStatusCast::PENDING,
                'amount' => $request->amountInPaisa,
                'fee' => 0,
                'tax' => 0,
                'net_amount' => $request->amountInPaisa,
                'currency' => $request->currency,
                'payment_method' => $request->method,
                'description' => $request->description,
                'purpose' => $request->purpose,
                'verified' => false,
                'expires_at' => $request->expiresInMinutes
                    ? now()->addMinutes($request->expiresInMinutes)
                    : null,
                'metadata' => $request->metadata,
            ]);

            return PaymentResponse::pending(
                message: 'Payment initiated. Awaiting confirmation.',
                transactionId: $transaction->uuid,
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed('Failed to create payment: '.$e->getMessage());
        }
    }

    /**
     * Manually confirm a pending payment (for admin use)
     */
    public function confirmPayment(string $transactionId): PaymentResponse
    {
        $transaction = Transaction::where('uuid', $transactionId)->first();

        if (! $transaction) {
            return PaymentResponse::failed('Transaction not found');
        }

        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return PaymentResponse::failed('Transaction is not pending');
        }

        try {
            DB::transaction(function () use ($transaction) {
                $wallet = $transaction->wallet;

                // Debit wallet
                $wallet->decrement('balance', $transaction->amount);
                $wallet->increment('total_debited', $transaction->amount);

                // Update transaction
                $transaction->update([
                    'status' => TransactionStatusCast::COMPLETED,
                    'verified' => true,
                    'verified_at' => now(),
                    'balance_after' => $wallet->balance,
                ]);
            });

            return PaymentResponse::success(
                message: 'Payment confirmed',
                transactionId: $transaction->uuid,
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed('Failed to confirm payment: '.$e->getMessage());
        }
    }

    /**
     * Cancel a pending payment order
     *
     * For native payments, this cancels the transaction in our database.
     */
    public function cancelOrder(string $orderId): bool
    {
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (! $transaction) {
            return false;
        }

        // Only cancel pending transactions
        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return $transaction->status === TransactionStatusCast::CANCELLED;
        }

        $transaction->update([
            'status' => TransactionStatusCast::CANCELLED,
            'notes' => 'Cancelled for payment retry',
        ]);

        return true;
    }
}
