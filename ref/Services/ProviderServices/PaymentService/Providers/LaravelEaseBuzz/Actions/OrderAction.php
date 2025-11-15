<?php

namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\OrderActionContract;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;

class OrderAction implements OrderActionContract
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



    public function create(array $data): false|string|null
    {
        return $this->api->initiatePaymentAPI($data);
    }

    /**
     * @return mixed
     */
    public function find()
    {
        // TODO: Implement find() method.
    }

    /**
     * @return mixed
     */
    public function all()
    {
        // TODO: Implement all() method.
    }
}
