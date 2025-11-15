<?php

namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;

class LinkAction
{


    protected LaravelEaseBuzzApi $api;

    public function __construct(LaravelEaseBuzzApi $api)
    {
        $this->api = $api;
    }

    public static function make(LaravelEaseBuzzApi $api):static
    {
        return new static($api);
    }


    public function create(array $data)
    {


        $payload = $data;
        $payload['key'] = $this->api->getKey();

        $hashAbleValue = $this->api->getKey() . '|' . $data['txnid'] . '|' . $data['amount']. '|' . $data['productinfo']. '|' . $data['firstname'].'|'.$data['email'].'|'.'|'.'|'.'|'.'|'.'|'.'|'.'|'.'|'.'|';

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($hashAbleValue)->getGeneratedAuthHash();
        $payload['hash'] = $authorization;
        //dd($payload);
        // Add headers and send the request
        return $this->api
            ->setBaseUrl('https://stoplight.io/mocks/easebuzz/payment-gateway/88397287/',true)
            ->version(null)
            ->fetchPost('payment/initiateLink', $payload, $authorization,false);

    }



}
