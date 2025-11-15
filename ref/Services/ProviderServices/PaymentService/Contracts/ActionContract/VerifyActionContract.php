<?php

namespace App\Services\ProviderServices\PaymentService\Contracts\ActionContract;

use App\Models\Wallet\Payment;
use Illuminate\Http\Request;

interface VerifyActionContract
{


    public function viaCallback(?Payment $payment,Request $request):bool;
    public function viaWebhook(Request $request):bool;

}
