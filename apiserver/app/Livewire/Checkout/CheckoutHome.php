<?php

namespace App\Livewire\Checkout;

use App\Models\Integration;
use App\Models\Transaction;
use App\View\Components\AppLayout;
use Livewire\Component;


class CheckoutHome extends Component
{

    protected Transaction $transaction;

    protected ?Integration $integration = null;

    protected ?string $providerComponent = null;


    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->transaction->load('integration');

        $this->integration = $this->transaction->integration;

        // Route to correct provider based on integration
        $this->analyzeTransactionAndProvider();
    }

    protected function analyzeTransactionAndProvider()
    {
        if (! $this->integration) {
            abort(404, 'Payment provider not configured');
        }

        // Determine provider based on integration slug
        return match ($this->integration->slug) {
            'cashfree' => $this->providerComponent = 'checkout.providers.cash-free-checkout',
            'razorpay' => $this->providerComponent = 'checkout.providers.razorpay-checkout',
            default => abort(404, 'Unsupported payment provider'),
        };
    }


    public function render()
    {
        return view('livewire.checkout.checkout-home',[
            'transaction' => $this->transaction,
            'integration' => $this->integration->except('credentials'),
            'providerComponent' => $this->providerComponent,
        ])->layout(AppLayout::class);
    }
}
