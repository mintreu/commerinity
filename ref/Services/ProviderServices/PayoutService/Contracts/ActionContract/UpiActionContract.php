<?php

namespace App\Services\ProviderServices\PayoutService\Contracts\ActionContract;

interface UpiActionContract
{


    public function send(int $amount);


}
