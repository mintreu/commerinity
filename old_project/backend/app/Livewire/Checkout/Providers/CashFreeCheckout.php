<?php

namespace App\Livewire\Checkout\Providers;

use Livewire\Component;
use Mintreu\LaravelTransaction\Models\Transaction;

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
            'returnUrl' => $this->failureUrl
        ];
    }

    public function render()
    {
        return view('livewire.checkout.providers.cash-free-checkout',[
            'payable' => !$this->transaction->verified,
            'mode'  => config('laravel-integration.providers.payments.cash-free.dev', true),
            'paymentSessionId' => $this->transaction->provider_gen_session, // Cashfree session
            'orderId'          => $this->transaction->provider_gen_id,      // Cashfree order
        ]);
    }


}
