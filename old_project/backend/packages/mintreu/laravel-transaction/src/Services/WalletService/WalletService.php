<?php

namespace Mintreu\LaravelTransaction\Services\WalletService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Casts\WalletStatusCast;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Models\Wallet;

class WalletService
{

    protected Wallet $wallet;
    protected int $amount = 0;
    protected string $successUrl;
    protected string $failureUrl;
    protected TransactionTypeCast $type;
    protected Integration $defaultPaymentIntegration;
    protected ?Transaction $transaction = null;


    /**
     * @param Wallet|Model $wallet
     */
    public function __construct(Wallet|Model $wallet)
    {
        $this->wallet = $wallet;
        $this->defaultPaymentIntegration = Integration::where('type',IntegrationTypeCast::PAYMENT->value)
            ->where('status',true)
            ->where('default',true)->first();
    }


    public static function make(Wallet|Model $wallet):static
    {
        return new static($wallet);
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public static function create(Model|Authenticatable $owner, ?string $pin = null): ?Wallet
    {
        // Try to parse DOB safely
        try {
            $dob = $owner->dob instanceof Carbon
                ? $owner->dob
                : (!empty($owner->dob) ? Carbon::parse($owner->dob) : null);
        } catch (\Throwable $e) {
            $dob = null;
        }

        // Predictable fallback PIN from DOB → MMYYYY or constant default
        $dobPin = $dob ? $dob->format('mY') : '123456';

        // Final PIN priority: user-provided > derived from DOB > fallback constant
        $finalPin = $pin ?? $dobPin;

        // Ensure exactly 6 digits
        $finalPin = preg_replace('/\D/', '', $finalPin); // digits only
        $finalPin = str_pad(substr($finalPin, 0, 6), 6, '0'); // pad or trim to 6

        // Create wallet record
        return $owner->wallet()->create([
            // 'uuid'     => (string) Str::uuid(), // optional
            'pin'      => $finalPin, // hashed by model mutator
            'balance'  => 0,
            'currency' => LaravelMoney::defaultCurrency(),
            'status'   => WalletStatusCast::ACTIVE,
        ]);
    }



    public function addFund(int $amount):static
    {
        $this->amount = $amount;
        $this->type = TransactionTypeCast::CREDITED;
        return $this;
    }

    public function getCheckoutUrl(string $redirect_success_url, string $redirect_failure_url):?string
    {
        $this->successUrl = $redirect_success_url;
        $this->failureUrl = $redirect_failure_url;


        $this->transaction = $this->wallet->createCreditTransaction(
            customer: $this->wallet->walletable,
            amount: $this->amount,
            redirect_success_url: $this->successUrl,
            redirect_failure_url: $this->failureUrl,
            wallet: $this->wallet,
            purpose: 'TopUp Wallet',
            paymentProviderSlug: $this->defaultPaymentIntegration->url,
            expireAfterMinutes: 60
        );

        if(!is_null($this->transaction))
        {
            return route('checkout',['transaction' => $this->transaction->uuid]);
        }
        return null;

    }



    // Verify Transactions
    public function validate(\Mintreu\LaravelTransaction\Models\Transaction $transaction)
    {
        $currentBalance = LaravelMoney::make($this->wallet->balance);


        if ($transaction->status->value == TransactionStatusCast::COMPLETED->value && $transaction->verified)
        {
            if (in_array($transaction->type->value,[TransactionTypeCast::CREDITED->value,TransactionTypeCast::REFUNDED->value]))
            {
                $newBalance = $currentBalance->plus($transaction->amount);
                $this->wallet->update([
                   'balance' => $newBalance->getAmount()
                ]);
            }else{
                $newBalance = $currentBalance->minus($transaction->amount);
                $this->wallet->update([
                    'balance' => $newBalance->getAmount()
                ]);
            }
            Event::dispatch('wallet.updated', [$this->wallet, $transaction]);
        }
    }



    // Sending Balance

    public function sendFund(string $receiver,int $amount,string $redirect_success_url, string $redirect_failure_url,?string $purpose = null)
    {
        $this->amount = $amount;
        $this->successUrl = $redirect_success_url;
        $this->failureUrl = $redirect_failure_url;

        $receiver = Wallet::firstWhere('uuid',$receiver);
        $currentBalance = LaravelMoney::make($this->wallet->balance);


        if ($currentBalance->greaterThanOrEqual(LaravelMoney::make($amount)))
        {
            // Deduction

            $this->transaction = $this->wallet->createDebitTransaction(
                customer: $this->wallet->walletable,
                amount: $this->amount,
                redirect_success_url: $this->successUrl,
                redirect_failure_url: $this->failureUrl,
                wallet: $this->wallet,
                purpose: $purpose ?? 'Send Money',
                paymentProviderSlug: 'wallet-payment',
                expireAfterMinutes: 60
            );

            $this->wallet->update([
                'balance' => $currentBalance->minus($this->amount)->getAmount()
            ]);


            // Add Balance To Receiver With Transaction
            $transaction = $receiver->createCreditTransaction(
                customer: $receiver->walletable,
                amount: $this->amount,
                redirect_success_url: $this->successUrl,
                redirect_failure_url: $this->failureUrl,
                wallet: $this->wallet,
                purpose: 'TopUp Wallet',
                paymentProviderSlug: 'wallet-payment',
                expireAfterMinutes: 60
            );


            $receiver->update([
                'balance' => LaravelMoney::make($receiver->balance)->plus($this->amount)->getAmount()
            ]);
        }


        return $this;
    }




    public function payFor(Model $payable_record,string $successUrl,string $failureUrl,string $amount_column = 'amount',?string $purpose = null)
    {
        $this->amount = $payable_record->{$amount_column};
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;

        $this->transaction = $this->wallet->createDebitTransaction(
            customer: $this->wallet->walletable,
            amount: $this->amount,
            redirect_success_url: $this->successUrl,
            redirect_failure_url: $this->failureUrl,
            wallet: $this->wallet,
            purpose: $purpose ?? 'Pay for '.$payable_record?->name ?? $payable_record?->title,
            paymentProviderSlug: 'wallet-payment',
            transactionable: $payable_record
        );

        $currentBalance = LaravelMoney::make($this->wallet->balance);
        $this->wallet->update([
            'balance' => $currentBalance->minus($this->amount)->getAmount()
        ]);
        // Make Confirm The Transaction
        $this->transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true
        ]);

        return $this;
    }








}
