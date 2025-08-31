<?php

namespace Mintreu\LaravelTransaction\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Models\Transaction;
use RuntimeException;

trait HasTransaction
{
    /**
     * Define the polymorphic relationship to a transaction.
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    /**
     * General method to create a transaction.
     */
    protected function createTransaction(
        Model $customer,                  // required
        TransactionTypeCast|string $type, // required
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,            // optional
        ?string $purpose = null,          // optional
        ?string $currency = null,         // optional
        ?string $paymentProviderSlug = null, // optional
        int $expireAfterMinutes = 20,     // optional with default
        array $notes = []                // optional with default
    ): ?Transaction {
        if ($type instanceof TransactionTypeCast) {
            $type = $type->value;
        }

        return DB::transaction(function () use ($type, $customer,$redirect_success_url,$redirect_failure_url, $wallet, $purpose, $currency, $paymentProviderSlug, $expireAfterMinutes,$notes) {


            $paymentProvider = $paymentProviderSlug
                ? LaravelIntegration::payment($paymentProviderSlug)
                : LaravelIntegration::payment();

            // Init Transaction Model
            $transactionData = [
                'uuid'      => $this->uuid,
                'type'      => $type,
                'purpose'   => $purpose,
                'integration_id'    => $paymentProvider->getModel()->id,
                'success_redirect_url' => $redirect_success_url,
                'failure_redirect_url'  => $redirect_failure_url
            ];

            // Attach wallet if integration is enabled
            if ($wallet && (config('laravel-transaction.wallet.status') ?? false)) {
                $transactionData['wallet_id'] = $wallet->id;
            }

            // Create transaction
            $transaction = $this->transaction()->create($transactionData);


            // Create provider order
            $providerData = $paymentProvider->order()->create(function (ProviderOrder $order) use ($customer, $currency,$notes,$expireAfterMinutes,$transaction) {
                $order->receipt($this->uuid)
                    ->currency($currency ?? LaravelMoney::defaultCurrency())
                    ->amount($this->resolveTransactionAmount())
                    ->customer($customer)
                    ->expireAfter($expireAfterMinutes)
                    ->successUrl(route('transaction.validate',['transaction' => $transaction->uuid]))
                    ->failureUrl(route('transaction.failure',['transaction' => $transaction->uuid]))
                    ->notes($notes);
            });

            if (!isset($providerData['success']) || $providerData['success'] !== true) {
                throw new RuntimeException($providerData['error'] ?? 'Unknown payment provider error.');
            }

            // Base transaction payload
            $transactionUpdateData = array_merge([
                'expire_at' => now()->addMinutes($expireAfterMinutes),
            ], $providerData['data'] ?? []);



            // Update transaction
            $transaction->update($transactionUpdateData);
            $transaction->refresh();


            // Adjust wallet balance
            if ($wallet && isset($wallet->balance)) {
                if ($type === TransactionTypeCast::DEBITED->value) {
                    $wallet->decrement('balance', $this->amount);
                } elseif ($type === TransactionTypeCast::CREDITED->value) {
                    $wallet->increment('balance', $this->amount);
                }

                // Fire event / notification after wallet change
                Event::dispatch('wallet.updated', [$wallet, $transaction]);
            }

            // Fire event / notification after transaction created
            Event::dispatch('transaction.created', [$transaction]);

            return $transaction;
        });
    }

    public function createDebitTransaction(
        Model $customer,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15
    ): ?Transaction {
        return $this->createTransaction(
            $customer,
            TransactionTypeCast::DEBITED,
            $redirect_success_url,
            $redirect_failure_url,
            $wallet,
            $purpose,
            $currency,
            $paymentProviderSlug,
            $expireAfterMinutes
        );
    }

    public function createCreditTransaction(
        Model $customer,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15
    ): ?Transaction {
        return $this->createTransaction(
            $customer,
            TransactionTypeCast::CREDITED,
            $redirect_success_url,
            $redirect_failure_url,
            $wallet,
            $purpose,
            $currency,
            $paymentProviderSlug,
            $expireAfterMinutes
        );
    }


    /**
     * Verify a transaction based on provider callback/request.
     *
     * @param Request    $request   Laravel request object
     * @param array      $fields    Fields required for validation (default: ['provider_transaction_id','amount'])
     * @param Transaction|null $transaction Optional transaction to verify
     *
     * @return bool
     */
    public function verifyTransaction(Request $request, array $fields = ['provider_transaction_id','amount'], ?Transaction $transaction = null): bool
    {
        $data = $request->only($fields);

        if (!$transaction) {
            $transaction = $this->transaction()->where('provider_transaction_id', $data['provider_transaction_id'] ?? null)->first();
        }

        if (!$transaction) {
            throw new RuntimeException('Transaction not found for verification.');
        }

        // Basic field validation
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new RuntimeException("Missing required field: {$field}");
            }

            if ($transaction->$field != $data[$field]) {
                throw new RuntimeException("Field mismatch for {$field}");
            }
        }

        // Mark transaction as verified
        $transaction->verified = true;
        $transaction->save();

        // Fire event / notification after transaction verified
        Event::dispatch('transaction.verified', [$transaction]);

        return true;
    }











    /**
     * Resolve the transaction amount from the model.
     *
     * Priority:
     * 1. Explicit property: $this->transactionAmount
     * 2. Constant: TRANSACTION_AMOUNT_VALUE
     * 3. Common attributes: amount, price, total
     *
     * @return int|float
     *
     * @throws \RuntimeException if no amount is resolved
     */
    protected function resolveTransactionAmount(): int|float
    {
        // 1. Explicit property
        if (isset($this->transactionAmount)) {
            return $this->transactionAmount;
        }

        // 2. Constant
        if (defined(static::class.'::TRANSACTION_AMOUNT_VALUE')) {
            return constant(static::class.'::TRANSACTION_AMOUNT_VALUE');
        }

        // 3. Common attributes
        foreach (['amount', 'price', 'total'] as $field) {
            if (isset($this->{$field})) {
                return $this->{$field};
            }
        }

        // 4. If nothing found → throw explicit error
        throw new \RuntimeException(sprintf(
            "Unable to resolve transaction amount for [%s]. ".
            "Please define a [protected \$transactionAmount] property, ".
            "or a [const TRANSACTION_AMOUNT_VALUE], ".
            "or ensure the model has one of the fields: amount, price, total.",
            static::class
        ));
    }




}
