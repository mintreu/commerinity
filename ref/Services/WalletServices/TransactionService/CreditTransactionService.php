<?php

namespace App\Services\WalletServices\TransactionService;

use App\Filament\Common\Pages\Auth\Common\MyWallet;
use App\Models\Enums\Wallet\TransactionStatusCast;
use App\Models\Enums\Wallet\TransactionTypeCast;
use App\Models\Localization\Address;
use App\Models\Wallet\Payment;
use App\Models\Wallet\Wallet;
use App\Services\CheckoutService\CheckoutService;
use App\Services\MoneyService\Money;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Illuminate\Database\Eloquent\Model;

class CreditTransactionService extends CheckoutService
{


    /**
     * Handles the placement of a credit transaction.
     *
     * @throws \Throwable
     */
    public function placeTransaction(
        Wallet $wallet,
        int $amount,
        ?string $notes,
        Model $walletOwner,
        Address $address
    ): mixed {

        // Create transaction
        $newTransaction = $wallet->transactions()->create([
            'amount' => $amount,
            'notes' => $notes,
            'type' => TransactionTypeCast::CREDITED,
            'status' => TransactionStatusCast::PENDING,
            'provider_id' => PaymentService::make()->getModel()->id,
        ]);


        // Create payment record
        $newPayment = $this->getInitPayment($newTransaction,MyWallet::getUrl());
        return $this->getInitProviderOrder(
            payment: $newPayment,
            customer: $walletOwner,
            address: $address,
            checkoutInfo: 'transaction',
            successUrl: route('transaction.credit.confirm', ['payment' => $newPayment->uuid]),
            failureUrl: route('transaction.credit.cancel_by_user', ['payment' => $newPayment->uuid]),
            hostedCheckout: true
        );

    }

    public function confirmTransaction(Wallet $wallet, Payment $payment, array $data): bool
    {
        // Process the transaction confirmation logic
        try {
            // Example: Verify the payment and update the wallet balance
            //$transaction = $wallet->transactions()->where('uuid', $payment->provider_gen_id)->first();

            $transaction = $payment->payable;

            // Ensure the transaction exists
            if (!$transaction) {
                throw new \Exception('Transaction not found.');
            }

            // Assuming you want to mark the transaction as confirmed and update its status
            $transaction->status = TransactionStatusCast::COMPLETED;
            $transaction->save();

            // Optionally, you can add further business logic, such as updating the wallet balance.
            // Example: Increase the wallet balance based on the transaction amount
            $wallet->balance += $transaction->amount;
            $wallet->save();


            // Save Transaction Data into Payment Model
            $payment->update([
                'provider_transaction_id' => $data['easepayid'],
                'verified' => true,
                'provider_data' => $data
            ]);


            // Assuming the confirmation was successful, return true
            return true;
        } catch (\Exception $e) {
            // Handle any errors that may occur
            return false;
        }
    }





}
