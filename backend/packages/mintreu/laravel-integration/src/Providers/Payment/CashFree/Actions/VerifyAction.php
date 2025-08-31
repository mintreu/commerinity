<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider;

class VerifyAction
{


    protected CashFreePaymentProvider $provider;

    /**
     * @param CashFreePaymentProvider $provider
     */
    public function __construct(CashFreePaymentProvider $provider)
    {
        $this->provider = $provider;
    }


    public function viaCallBack(Request $request, ?Model $transaction = null):bool
    {
        // Check The Transaction Paid Or Not

        $orderData = $this->provider->order()->fetch($transaction->provider_gen_id);

        return strtolower($orderData['data']['order_status']) == 'paid';

    }




}
