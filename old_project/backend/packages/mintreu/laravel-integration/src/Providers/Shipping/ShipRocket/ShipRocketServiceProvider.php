<?php

namespace Mintreu\LaravelIntegration\Providers\Shipping\ShipRocket;

class ShipRocketServiceProvider
{

    protected ?string $key = null;
    protected ?string $secret = null;

    /**
     * @param string|null $key
     * @param string|null $secret
     */
    public function __construct(?string $key, ?string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public static function make(?string $key, ?string $secret):static
    {
        return new static($key,$secret);
    }


    public function getSlug():string
    {
        return 'shiprocket-shipping';
    }




    public function order()
    {
        return new Actions\OrderAction($this->api);
    }

    public function courier()
    {
        return new Actions\CourierAction($this->api);
    }

    public function return()
    {
        return new Actions\ReturnAction($this->api);
    }

    public function shipment()
    {
        // TODO: Implement shipment() method.
    }

    public function tracking()
    {
        return new Actions\TrackingAction($this->api);
    }






}
