<?php

namespace App\Livewire\Checkout;

use App\View\Components\AppLayout;
use Livewire\Component;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelTransaction\Models\Transaction;

class CheckoutHome extends Component
{

    protected Transaction $transaction;

    protected ?Integration $integration = null;


    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;

//        abort_if($this->transaction->verified|| $this->transaction->expire_at > now(),404);
        $this->transaction->load('integration');

        $this->integration = $this->transaction->integration;
        //abort_unless(is_null($this->integration),404);
        $this->analyzeTransactionAndProvider();
    }

    protected function analyzeTransactionAndProvider()
    {

    }




    public function render()
    {
        return view('livewire.checkout.checkout-home',[
            'transaction' => $this->transaction,
            'integration' => $this->integration->except('key','secret','webhook')
        ])->layout(AppLayout::class);
    }
}
