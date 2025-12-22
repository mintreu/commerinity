<?php

namespace App\Livewire\Checkout\Providers;

use Livewire\Component;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Models\Transaction;

class RazorpayCheckout extends Component
{
    protected Transaction $transaction;
    public string $failureUrl;
    public array $configuration;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->transaction->load('transactionable.customer');

        $this->configuration = $this->getProviderConfig();
        $this->failureUrl = $this->transaction->failure_url;

    }

    protected function getProviderConfig(): array
    {
        // Normalize customer info
        $transactionable = $this->transaction->transactionable;

        $customerInfo = [
            'name'    => $transactionable->name ?? $transactionable->customer_name,
            'email'   => $transactionable->email ?? $transactionable->customer_email,
            'contact' => $transactionable->mobile ?? $transactionable->customer_mobile,
        ];

        return [
            'key'         => $this->transaction->integration->key
                ?? config('laravel-integration.providers.payment.razorpay.key'),
            'amount'      => $this->transaction->amount,
            'currency'    => LaravelMoney::defaultCurrency(),
            'name'        => config('app.name'),
            'description' => ($customerInfo['name'] ?? 'Customer') . ' Summary',
            'image'       => '',
            'order_id'    => $this->transaction->provider_gen_id,
            'callback_url'=> $this->transaction->callback_url,

            // ✅ Reuse optimized customer info
            'prefill'     => $customerInfo,

            'theme'       => [
                'color' => '#410254',
            ],
        ];
    }



    public function render()
    {
        return view('livewire.checkout.providers.razorpay-checkout',[
            'payable' => !$this->transaction->verified
        ]);
    }
}
