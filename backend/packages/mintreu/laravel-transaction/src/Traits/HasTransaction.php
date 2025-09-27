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
        null|int|float $amount = null
    ): ?Transaction {
        if ($type instanceof TransactionTypeCast) {
            $type = $type->value;
        }

        return DB::transaction(function () use ($type, $customer, $redirect_success_url, $redirect_failure_url, $wallet, $purpose, $currency, $paymentProviderSlug, $expireAfterMinutes, $notes, $amount) {

            $paymentProvider = $paymentProviderSlug
                ? LaravelIntegration::payment($paymentProviderSlug)
                : LaravelIntegration::payment();



            // Resolve amount with backward compatibility
            $resolvedAmount = $amount ?? $this->resolveTransactionAmount();

            $transactionData = [
                'uuid'      => $this->uuid,
                'type'      => $type,
                'purpose'   => $purpose,
                'amount'    => $resolvedAmount,
                'integration_id'    => $paymentProvider->getModel()->id,
                'success_redirect_url' => $redirect_success_url,
                'failure_redirect_url'  => $redirect_failure_url
            ];

            if ($wallet && (config('laravel-transaction.wallet.status') ?? false)) {
                $transactionData['wallet_id'] = $wallet->id;
            }




            $transaction = $this->transaction()->create($transactionData);



            $providerData = $paymentProvider->order()->create(function (ProviderOrder $order) use ($customer, $currency, $notes, $expireAfterMinutes, $transaction, $resolvedAmount) {
                $order->receipt($this->uuid)
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
                // optionally persist amount used for the transaction if your table has such a column
               // 'amount' => $resolvedAmount,
            ], $providerData['data'] ?? []);

            $transaction->update($transactionUpdateData);
            $transaction->refresh();


            // Adjust wallet balance using the resolved amount
            if ($wallet && isset($wallet->balance)) {
                if ($type === TransactionTypeCast::DEBITED->value) {
                    $wallet->decrement('balance', $resolvedAmount);
                } elseif ($type === TransactionTypeCast::CREDITED->value) {
                    $wallet->increment('balance', $resolvedAmount);
                }
                Event::dispatch('wallet.updated', [$wallet, $transaction]);
            }

            Event::dispatch('transaction.created', [$transaction]);

            return $transaction;
        });
    }

    public function createDebitTransaction(
        Model|array $customer,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15,
        null|int|float $amount = null
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
            $expireAfterMinutes,
            notes: [],
            amount: $amount // pass through
        );
    }

    public function createCreditTransaction(
        Model|array $customer,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Model $wallet = null,
        ?string $purpose = null,
        ?string $currency = null,
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 15,
        null|int|float $amount = null
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
            $expireAfterMinutes,
            notes: [],
            amount: $amount // pass through
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
            $field = constant(static::class.'::TRANSACTION_AMOUNT_VALUE');
            return $this->{$field};
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
