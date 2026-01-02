<?php

namespace App\Livewire\Checkout\Providers;

use App\Models\Transaction;
use Livewire\Component;

class CashFreeCheckout extends Component
{


    protected Transaction $transaction;
    public string $failureUrl;
    public string $successUrl;
    public array $configuration;

    public function mount(Transaction $transaction)
    {

        $this->transaction = $transaction;
        $this->transaction->load('transactionable.customer');

        $this->failureUrl = $this->transaction->failure_url;
        $this->successUrl = $this->transaction->success_url;

        $this->configuration = $this->getProviderConfig();

    }

    private function getProviderConfig():array
    {
        return [
            'paymentSessionId' => $this->transaction->provider_gen_session,
//            'returnUrl' => $this->failureUrl,
            'returnUrl' => route('transaction.failure', ['transaction' => $this->transaction->uuid])
        ];
    }

    public function render()
    {
        return view('livewire.checkout.providers.cash-free-checkout',[
            'payable' => !$this->transaction->verified,
            'mode'  => config('laravel-integration.providers.payments.cash-free.dev', true),
            'paymentSessionId' => $this->transaction->provider_gen_session, // Cashfree session
            'orderId'          => $this->transaction->provider_gen_id,      // Cashfree order
            'allowed_for_checkout' => !$this->transaction->verified && !$this->transaction->expires_at->isPast(),
            'returnFromProviderUrl' => route('transaction.validate', ['transaction' => $this->transaction->uuid])
        ]);
    }


}
