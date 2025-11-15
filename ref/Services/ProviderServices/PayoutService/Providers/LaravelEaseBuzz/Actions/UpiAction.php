<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\UpiActionContract;
use Illuminate\Database\Eloquent\Model;

class UpiAction implements UpiActionContract
{


    protected LaravelEaseBuzzApi $api;
    protected Model $user;

    public function __construct(LaravelEaseBuzzApi $api,Model $user)
    {
        $this->api = $api;
        $this->user = $user;
    }

    public static function make(LaravelEaseBuzzApi $api,Model $user):static
    {
        return new static($api,$user);
    }







    /**
     * @return mixed
     */
    public function send(int $amount)
    {
        // TODO: Implement send() method.
    }
}
