<?php

namespace App\Services\WalletServices;

use App\Models\Enums\Wallet\TransactionStatusCast;
use App\Models\Enums\Wallet\TransactionTypeCast;
use App\Models\Localization\Address;
use App\Models\Wallet\Payment;
use App\Models\Wallet\Wallet;
use App\Services\ProviderServices\PaymentService\PaymentService;
use App\Services\WalletServices\TransactionService\CreditTransactionService;
use Illuminate\Database\Eloquent\Model;

class WalletService
{

    protected Wallet $record;
    protected Model $walletOwner;
    protected ?Address $address = null;

    /**
     * @param Wallet $record
     */
    public function __construct(Wallet $record)
    {
        $this->record = $record->loadMissing('walletable');

        $this->walletOwner = $this->record->walletable;

        $this->address = $this->walletOwner?->addresses()->with(['state', 'country'])->first();
    }



    public static function make(Wallet|Model $wallet): static
    {
        $instance = new static($wallet);
        return $instance;
    }


    /**
     * @throws \Throwable
     */
    public function addBalance(int $amount, ?string $notes = null): mixed
    {
        throw_unless($this->address, 'wallet owner has no address');

        $creditTransactionService = CreditTransactionService::make();

        return $creditTransactionService->placeTransaction(
            wallet: $this->record,
            amount: $amount,
            notes: $notes,
            walletOwner: $this->walletOwner,
            address: $this->address
        );
    }

    public function confirmAddBalance(Payment $payment,array $data):bool
    {
        $creditTransactionService = CreditTransactionService::make();
        return $creditTransactionService->confirmTransaction(
            wallet: $this->record,
            payment: $payment,
            data: $data
        );
    }




    public function withdrawBalance(int $amount)
    {
        dd($amount);
    }

    public function sendBalance()
    {
        dd('here');
    }

    public function requestBalance()
    {

    }


    public function makePayment()
    {

    }



}
