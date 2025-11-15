<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\PayoutActionContract;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;

class PayoutAction implements PayoutActionContract
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



    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->api->payoutAPI($data);
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
