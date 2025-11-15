<?php

namespace App\Services\ProviderServices\PaymentService\Contracts;


use App\Services\ProviderServices\Contract\PaymentProviderServiceContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\OrderActionContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\VerifyActionContract;
use Illuminate\Database\Eloquent\Model;

interface PaymentServiceContract extends PaymentProviderServiceContract
{

    public function order(): OrderActionContract;

    public function verify():VerifyActionContract;

    public function link();


}
