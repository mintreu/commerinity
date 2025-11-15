<?php

namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions;

use App\Models\Wallet\Payment;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\VerifyActionContract;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;
use Illuminate\Http\Request;

class VerifyAction implements VerifyActionContract
{

    protected PayWithEasebuzzLib $api;


    public function __construct(PayWithEasebuzzLib $api)
    {
        $this->api = $api;
    }


    public static function make(PayWithEasebuzzLib $api):static
    {
        return new static($api);
    }


    public function viaCallback(?Payment $payment,Request $request):bool
    {
        $callbackData = $request->all();
        if ($callbackData['status'] == 'success' ?? false)
        {
            $payment->update([
                'provider_transaction_id' => $callbackData['easepayid'],
                'verified' => true,
                'provider_data' => $callbackData
            ]);
        }
        return $payment->verified;
    }



    public function viaWebhook(Request $request):bool
    {

    }


}
