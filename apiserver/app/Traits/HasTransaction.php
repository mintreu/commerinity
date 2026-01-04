<?php

declare(strict_types=1);

namespace App\Traits;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\PaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * HasTransaction Trait
 *
 * Makes any model payable using the unified PaymentService architecture.
 * Supports all providers: Native (wallet), Cashfree, Razorpay
 *
 * Usage:
 * - Add to model: use HasTransaction;
 * - Define constant: const TRANSACTION_AMOUNT_COLUMN = 'total';
 * - Create transaction: $model->createDebitTransaction(...)
 */
trait HasTransaction
{
    /**
     * Get the transaction for this model
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    /**
     * Create a debit transaction (user pays)
     *
     * @param  Model|array  $customer  Customer model or array
     * @param  PaymentMethodCast  $paymentMethod  Payment method (wallet/cashfree/razorpay/etc)
     * @param  string  $redirectSuccessUrl  Success redirect
     * @param  string  $redirectFailureUrl  Failure redirect
     * @param  Wallet|null  $wallet  User's wallet
     * @param  string|null  $purpose  Transaction description
     * @param  int  $expireAfterMinutes  Expiry time
     *
     * @throws \Exception
     */
    public function createDebitTransaction(
        Model|array $customer,
        PaymentMethodCast $paymentMethod,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        int $expireAfterMinutes = 60
    ): Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::DEBIT,
            paymentMethod: $paymentMethod,
            redirectSuccessUrl: $redirectSuccessUrl,
            redirectFailureUrl: $redirectFailureUrl,
            wallet: $wallet,
            purpose: $purpose,
            expireAfterMinutes: $expireAfterMinutes
        );
    }

    /**
     * Create a credit transaction (wallet topup, refund)
     *
     * @param  Model|array  $customer  Customer model or array
     * @param  int  $amount  Amount in paisa
     * @param  PaymentMethodCast  $paymentMethod  Payment method
     * @param  string  $redirectSuccessUrl  Success redirect
     * @param  string  $redirectFailureUrl  Failure redirect
     * @param  Wallet|null  $wallet  User's wallet
     * @param  string|null  $purpose  Transaction description
     * @param  int  $expireAfterMinutes  Expiry time
     *
     * @throws \Exception
     */
    public function createCreditTransaction(
        Model|array $customer,
        int $amount,
        PaymentMethodCast $paymentMethod,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        int $expireAfterMinutes = 60
    ): Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::CREDIT,
            paymentMethod: $paymentMethod,
            redirectSuccessUrl: $redirectSuccessUrl,
            redirectFailureUrl: $redirectFailureUrl,
            wallet: $wallet,
            purpose: $purpose,
            expireAfterMinutes: $expireAfterMinutes,
            amount: $amount
        );
    }

    /**
     * Core transaction creation method using PaymentService
     *
     * @throws \Exception
     */
    protected function createTransaction(
        Model|array $customer,
        TransactionTypeCast $type,
        PaymentMethodCast $paymentMethod,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        int $expireAfterMinutes = 60,
        ?int $amount = null
    ): Transaction {
        return DB::transaction(function () use (
            $customer,
            $type,
            $paymentMethod,
            $redirectSuccessUrl,
            $redirectFailureUrl,
            $wallet,
            $purpose,
            $expireAfterMinutes,
            $amount
        ) {
            // 1. Resolve amount
            $resolvedAmount = $amount ?? $this->resolveTransactionAmount();

            // 2. Parse customer details
            $customerData = $this->parseCustomerData($customer);




            // 3. Create transaction record using MorphOne relationship from trait
            // Automatically sets transactionable_type and transactionable_id
            $transaction = $this->transaction()->create([
                'uuid' => 'TXN-'.Str::upper(Str::random(12)),
                'wallet_id' => $wallet?->id,
                'type' => $type,
                'status' => TransactionStatusCast::PENDING,
                'amount' => $resolvedAmount,
                'currency' => 'INR',
                'payment_method' => $paymentMethod,
                'purpose' => $purpose ?? 'Payment',
                'description' => $customerData['name'].' - '.($purpose ?? 'Payment'),
                'expires_at' => now()->addMinutes($expireAfterMinutes),
                'verified' => false,
                'integration_id' => null,
                'success_url' => $redirectSuccessUrl,
                'failure_url' =>$redirectFailureUrl,
                'transactionable_type' => get_class($this),
                'transactionable_id' => $this->id,
                'metadata' => [
                    'customer' => $customerData,
//                    'redirect_success_url' => $redirectSuccessUrl,
//                    'redirect_failure_url' => $redirectFailureUrl,
                ],
            ]);




            // 4. Use PaymentService to initiate payment (provider-agnostic)
            $paymentService = app(PaymentService::class);

            $callbackUrl = route('transaction.validate', ['transaction' => $transaction->uuid]);

            $paymentRequest = new PaymentInitiateRequest(
                amountInPaisa: $resolvedAmount,
                currency: 'INR',
                method: $paymentMethod,
                userFingerprint: $customerData['user_fingerprint'],
                userId: $customerData['user_id'] ?? 0,
                walletId: $wallet?->id ?? 0,
                transactionId: $transaction->uuid,
                customerName: $customerData['name'],
                customerEmail: $customerData['email'],
                customerPhone: $customerData['mobile'],
                purpose: $purpose,
                description: $transaction->description,
                metadata: $transaction->metadata ?? [],
                callbackUrl: $callbackUrl,
                expiresInMinutes: $expireAfterMinutes
            );

            $paymentResponse = $paymentService->initiate($paymentRequest);

            // 5. Update transaction with provider response
            if ($paymentResponse->success || $paymentResponse->status === 'pending') {
                $transaction->update([
                    'provider_order_id' => $paymentResponse->providerOrderId,
                    'provider_transaction_id' => $paymentResponse->providerTransactionId,
                    'provider_gen_id' => $paymentResponse->metadata['provider_gen_id'] ?? null,
                    'provider_gen_session' => $paymentResponse->metadata['provider_gen_session'] ?? null,
                    'provider_gen_link' => $paymentResponse->metadata['provider_gen_link'] ?? null,
                    'checkout_type' => 'web',
                    'status' => $paymentResponse->getStatusEnum(),
                    'verified' => $paymentResponse->status === 'success',
                    'verified_at' => $paymentResponse->status === 'success' ? now() : null,
                    'provider_response' => $paymentResponse->metadata,
                    'integration_id' => $paymentResponse->metadata['integration_id']  // required must need
                ]);
            } else {
                // Failed to create payment
                $transaction->update([
                    'status' => TransactionStatusCast::FAILED,
                    'provider_response' => $paymentResponse->metadata,
                ]);

                throw new \Exception('Failed to create payment: '.$paymentResponse->message);
            }

            return $transaction->fresh();
        });
    }

    /**
     * Resolve transaction amount from model
     */
    protected function resolveTransactionAmount(): int
    {
        // Try constant first
        if (defined('static::TRANSACTION_AMOUNT_COLUMN')) {
            $column = static::TRANSACTION_AMOUNT_COLUMN;
            if (isset($this->{$column})) {
                return (int) $this->{$column};
            }
        }

        // Try common column names
        foreach (['total', 'amount', 'fee', 'price'] as $column) {
            if (isset($this->{$column})) {
                return (int) $this->{$column};
            }
        }

        throw new \Exception('Unable to resolve transaction amount. Define TRANSACTION_AMOUNT_COLUMN constant.');
    }

    /**
     * Check if this model has any pending or successful payment transaction
     */
    public function hasPaymentTransaction(): bool
    {
        return $this->transaction()
            ->whereIn('status', [
                TransactionStatusCast::PENDING,
                TransactionStatusCast::SUCCESS,
            ])
            ->exists();
    }

    /**
     * Get the current active payment transaction
     */
    public function getActivePaymentTransaction(): ?Transaction
    {
        return $this->transaction()
            ->whereIn('status', [
                TransactionStatusCast::PENDING,
                TransactionStatusCast::SUCCESS,
            ])
            ->first();
    }

    /**
     * Parse customer data from model or array
     */
    protected function parseCustomerData(Model|array $customer): array
    {
        if (is_array($customer)) {
            return [
                'user_id' => $customer['id'] ?? 0,
                'user_fingerprint' => $customer['fingerprint'] ?? null,
                'name' => $customer['name'] ?? 'Guest',
                'email' => $customer['email'] ?? null,
                'mobile' => $customer['mobile'] ?? null,
            ];
        }

        $fingerprint = method_exists($customer, 'fingerprint') ? $customer->fingerprint() : null;

        return [
            'user_id' => $customer->id ?? 0,
            'user_fingerprint' => $fingerprint,
            'name' => $customer->name ?? 'User',
            'email' => $customer->email ?? null,
            'mobile' => $customer->mobile ?? null,
        ];
    }
}
