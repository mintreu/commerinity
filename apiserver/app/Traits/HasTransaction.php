<?php

declare(strict_types=1);

namespace App\Traits;

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Payment\CashfreeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * HasTransaction Trait
 *
 * Makes any model payable by adding polymorphic transaction relationship
 * and helper methods to create debit/credit transactions.
 *
 * Usage:
 * - Add to model: use HasTransaction;
 * - Define constant: const TRANSACTION_AMOUNT_COLUMN = 'total'; // or 'amount'
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
     * @param  Model|array  $customer  Customer model or array with name/email/mobile
     * @param  string  $redirectSuccessUrl  Where to redirect on success
     * @param  string  $redirectFailureUrl  Where to redirect on failure
     * @param  Wallet|null  $wallet  User's wallet (for wallet payments)
     * @param  string|null  $purpose  Transaction description
     * @param  string|null  $paymentProviderSlug  Provider slug (e.g., 'cashfree')
     * @param  int  $expireAfterMinutes  Transaction expiry time
     *
     * @throws \Exception
     */
    public function createDebitTransaction(
        Model|array $customer,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 60
    ): Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::DEBIT,
            redirectSuccessUrl: $redirectSuccessUrl,
            redirectFailureUrl: $redirectFailureUrl,
            wallet: $wallet,
            purpose: $purpose,
            paymentProviderSlug: $paymentProviderSlug,
            expireAfterMinutes: $expireAfterMinutes
        );
    }

    /**
     * Create a credit transaction (wallet topup, refund)
     *
     * @param  Model|array  $customer  Customer model or array
     * @param  int  $amount  Amount in paisa
     * @param  string  $redirectSuccessUrl  Success redirect
     * @param  string  $redirectFailureUrl  Failure redirect
     * @param  Wallet|null  $wallet  User's wallet
     * @param  string|null  $purpose  Transaction description
     * @param  string|null  $paymentProviderSlug  Provider slug
     * @param  int  $expireAfterMinutes  Expiry time
     *
     * @throws \Exception
     */
    public function createCreditTransaction(
        Model|array $customer,
        int $amount,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 60
    ): Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::CREDIT,
            redirectSuccessUrl: $redirectSuccessUrl,
            redirectFailureUrl: $redirectFailureUrl,
            wallet: $wallet,
            purpose: $purpose,
            paymentProviderSlug: $paymentProviderSlug,
            expireAfterMinutes: $expireAfterMinutes,
            amount: $amount
        );
    }

    /**
     * Core transaction creation method
     *
     * @throws \Exception
     */
    protected function createTransaction(
        Model|array $customer,
        TransactionTypeCast $type,
        string $redirectSuccessUrl,
        string $redirectFailureUrl,
        ?Wallet $wallet = null,
        ?string $purpose = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 60,
        ?int $amount = null
    ): Transaction {
        return DB::transaction(function () use (
            $customer,
            $type,
            $redirectSuccessUrl,
            $redirectFailureUrl,
            $wallet,
            $purpose,
            $paymentProviderSlug,
            $expireAfterMinutes,
            $amount
        ) {
            // 1. Get payment provider (default: Cashfree)
            $integration = Integration::query()
                ->ofType(Integration::TYPE_PAYMENT)
                ->active()
                ->when($paymentProviderSlug, fn ($q) => $q->bySlug($paymentProviderSlug))
                ->when(! $paymentProviderSlug, fn ($q) => $q->where('is_default', true))
                ->firstOrFail();

            // 2. Resolve amount (from model or parameter)
            $resolvedAmount = $amount ?? $this->resolveTransactionAmount();

            // 3. Parse customer details
            $customerData = $this->parseCustomerData($customer);

            // 4. Create transaction record
            $transaction = $this->transaction()->create([
                'uuid' => 'TXN-'.Str::upper(Str::random(12)),
                'wallet_id' => $wallet?->id,
                'type' => $type,
                'status' => TransactionStatusCast::PENDING,
                'amount' => $resolvedAmount,
                'currency' => 'INR',
                'integration_id' => $integration->id,
                'purpose' => $purpose ?? 'Payment',
                'description' => $customerData['name'].' - '.$purpose,
                'expires_at' => now()->addMinutes($expireAfterMinutes),
                'is_verified' => false,
                'metadata' => [
                    'customer' => $customerData,
                    'redirect_success_url' => $redirectSuccessUrl,
                    'redirect_failure_url' => $redirectFailureUrl,
                ],
            ]);

            // 5. Create Cashfree order and get payment session
            $cashfreeService = app(CashfreeService::class);
            $cashfreeOrder = $cashfreeService->createOrder(
                transaction: $transaction,
                integration: $integration,
                customerData: $customerData,
                redirectSuccessUrl: $redirectSuccessUrl,
                redirectFailureUrl: $redirectFailureUrl
            );

            // 6. Update transaction with Cashfree response
            if ($cashfreeOrder['success']) {
                $transaction->update([
                    'provider_order_id' => $cashfreeOrder['cf_order_id'],
                    'checkout_url' => $cashfreeOrder['payment_session_id'], // ⚠️ Storing in checkout_url for now
                    'provider_response' => $cashfreeOrder,
                ]);
            } else {
                // Failed to create order
                $transaction->update([
                    'status' => TransactionStatusCast::FAILED,
                    'provider_response' => $cashfreeOrder,
                ]);

                throw new \Exception('Failed to create Cashfree order: '.$cashfreeOrder['error']);
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
     * Parse customer data from model or array
     */
    protected function parseCustomerData(Model|array $customer): array
    {
        if (is_array($customer)) {
            return [
                'name' => $customer['name'] ?? 'Guest',
                'email' => $customer['email'] ?? null,
                'mobile' => $customer['mobile'] ?? null,
            ];
        }

        return [
            'name' => $customer->name ?? 'User',
            'email' => $customer->email ?? null,
            'mobile' => $customer->mobile ?? null,
        ];
    }
}
