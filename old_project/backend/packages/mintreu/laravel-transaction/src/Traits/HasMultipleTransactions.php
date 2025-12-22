<?php

namespace Mintreu\LaravelTransaction\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Models\Transaction;
use RuntimeException;

trait HasMultipleTransactions
{
    /**
     * Define the polymorphic relationship to multiple transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    /**
     * Get the latest transaction.
     */
    public function latestTransaction(): ?Transaction
    {
        return $this->transactions()->latest()->first();
    }

    /**
     * Get completed transactions.
     */
    public function completedTransactions()
    {
        return $this->transactions()->where('status', 'completed');
    }

    /**
     * Get pending transactions.
     */
    public function pendingTransactions()
    {
        return $this->transactions()->where('status', 'pending');
    }

    /**
     * Get total transaction amount by type.
     */
    public function transactionTotal(string $type = null): float
    {
        $query = $this->transactions()->where('status', 'completed');

        if ($type) {
            $query->where('type', $type);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get credit transaction total.
     */
    public function creditTotal(): float
    {
        return $this->transactionTotal(TransactionTypeCast::CREDITED->value);
    }

    /**
     * Get debit transaction total.
     */
    public function debitTotal(): float
    {
        return $this->transactionTotal(TransactionTypeCast::DEBITED->value);
    }

    /**
     * Calculate net balance from transactions.
     */
    public function transactionBalance(): float
    {
        return $this->creditTotal() - $this->debitTotal();
    }

    /**
     * General method to create a transaction.
     *
     * @param Model|array $customer Customer model or array with name, email, mobile
     * @param TransactionTypeCast|string $type Transaction type (credited/debited)
     * @param string $redirect_success_url Success redirect URL
     * @param string $redirect_failure_url Failure redirect URL
     * @param Model|null $wallet Wallet model (optional)
     * @param string|null $purpose Transaction purpose description
     * @param string|null $currency Currency code
     * @param string|null $paymentProviderSlug Payment provider slug
     * @param int $expireAfterMinutes Transaction expiry time in minutes
     * @param array $notes Additional notes for provider
     * @param int|float $amount Transaction amount
     * @param Model|null $transactionable Custom transactionable model (defaults to $this)
     * @return Transaction|null Created transaction instance
     * @throws RuntimeException
     */
    protected function createTransaction(
        Model|array $customer,
        TransactionTypeCast|string $type,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 20,
        array $notes = [],
        int|float $amount = 0,
        ?Model $transactionable = null
    ): ?Transaction {
        if ($type instanceof TransactionTypeCast) {
            $type = $type->value;
        }

        return DB::transaction(function () use (
            $type,
            $customer,
            $redirect_success_url,
            $redirect_failure_url,
            $wallet,
            $purpose,
            $currency,
            $paymentProviderSlug,
            $expireAfterMinutes,
            $notes,
            $amount,
            $transactionable
        ) {
            $paymentProvider = $paymentProviderSlug
                ? LaravelIntegration::payment($paymentProviderSlug)
                : LaravelIntegration::payment();

            // Resolve amount
            $resolvedAmount = is_int($amount) ? $amount : LaravelMoney::make($amount)->getAmount();

            // Determine transactionable: use provided model or fallback to $this
            $transactionableModel = $transactionable ?? $this;

            $transactionData = [
                'type'      => $type,
                'purpose'   => $purpose,
                'amount'    => $resolvedAmount,
                'integration_id'    => $paymentProvider->getModel()->id,
                'success_redirect_url' => $redirect_success_url,
                'failure_redirect_url'  => $redirect_failure_url,
                'status'    => TransactionStatusCast::PENDING->value,
                'transactionable_type' => get_class($transactionableModel),
                'transactionable_id'   => $transactionableModel->id,
            ];

            if ($wallet && (config('laravel-transaction.wallet.status') ?? false)) {
                $transactionData['wallet_id'] = $wallet->id;
            }

            // Create transaction with morph fields explicitly set
            $transaction = Transaction::create($transactionData);

            $providerData = $paymentProvider->order()->create(function (ProviderOrder $order) use (
                $customer,
                $currency,
                $notes,
                $expireAfterMinutes,
                $transaction
            ) {
                $order->receipt($transaction->uuid)
                    ->currency($currency ?? LaravelMoney::defaultCurrency())
                    ->amount($transaction->amount)
                    ->expireAfter($expireAfterMinutes)
                    ->successUrl(route('transaction.validate', ['transaction' => $transaction->uuid]))
                    ->failureUrl(route('transaction.failure', ['transaction' => $transaction->uuid]))
                    ->notes($notes);

                if (!is_array($customer) && $customer instanceof Model) {
                    $order->customer($customer);
                } elseif (is_array($customer)) {
                    $order->customerName($customer['name'])
                        ->customerEmail($customer['email'])
                        ->customerMobile($customer['mobile']);
                }
            });

            if (!isset($providerData['success']) || $providerData['success'] !== true) {
                throw new RuntimeException($providerData['error'] ?? 'Unknown payment provider error.');
            }

            $transactionUpdateData = array_merge([
                'expire_at' => now()->addMinutes($expireAfterMinutes),
            ], $providerData['data'] ?? []);

            $transaction->update($transactionUpdateData);

            Event::dispatch('transaction.created', [$transaction]);

            return $transaction;
        });
    }

    /**
     * Create a debit transaction.
     *
     * @param Model|array $customer Customer model or array with name, email, mobile
     * @param int|float $amount Transaction amount
     * @param string $redirect_success_url Success redirect URL
     * @param string $redirect_failure_url Failure redirect URL
     * @param Model|null $wallet Wallet model (optional)
     * @param string|null $purpose Transaction purpose description
     * @param string|null $currency Currency code
     * @param string|null $paymentProviderSlug Payment provider slug
     * @param int $expireAfterMinutes Transaction expiry time in minutes
     * @param Model|null $transactionable Custom transactionable model (defaults to $this)
     * @return Transaction|null Created debit transaction
     */
    public function createDebitTransaction(
        Model|array $customer,
        int|float $amount,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15,
        ?Model $transactionable = null
    ): ?Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::DEBITED,
            redirect_success_url: $redirect_success_url,
            redirect_failure_url: $redirect_failure_url,
            wallet: $wallet,
            purpose: $purpose,
            currency: $currency,
            paymentProviderSlug: $paymentProviderSlug,
            expireAfterMinutes: $expireAfterMinutes,
            notes: [],
            amount: $amount,
            transactionable: $transactionable
        );
    }

    /**
     * Create a credit transaction.
     *
     * @param Model|array $customer Customer model or array with name, email, mobile
     * @param int|float $amount Transaction amount
     * @param string $redirect_success_url Success redirect URL
     * @param string $redirect_failure_url Failure redirect URL
     * @param Model|null $wallet Wallet model (optional)
     * @param string|null $purpose Transaction purpose description
     * @param string|null $currency Currency code
     * @param string|null $paymentProviderSlug Payment provider slug
     * @param int $expireAfterMinutes Transaction expiry time in minutes
     * @param Model|null $transactionable Custom transactionable model (defaults to $this)
     * @return Transaction|null Created credit transaction
     */
    public function createCreditTransaction(
        Model|array $customer,
        int|float $amount,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15,
        ?Model $transactionable = null
    ): ?Transaction {
        return $this->createTransaction(
            customer: $customer,
            type: TransactionTypeCast::CREDITED,
            redirect_success_url: $redirect_success_url,
            redirect_failure_url: $redirect_failure_url,
            wallet: $wallet,
            purpose: $purpose,
            currency: $currency,
            paymentProviderSlug: $paymentProviderSlug,
            expireAfterMinutes: $expireAfterMinutes,
            notes: [],
            amount: $amount,
            transactionable: $transactionable
        );
    }

    /**
     * Get transaction by UUID.
     */
    public function getTransactionByUuid(string $uuid): ?Transaction
    {
        return $this->transactions()->where('uuid', $uuid)->first();
    }

    /**
     * Get transaction by provider ID.
     */
    public function getTransactionByProviderId(string $providerId): ?Transaction
    {
        return $this->transactions()->where('provider_transaction_id', $providerId)->first();
    }
}
