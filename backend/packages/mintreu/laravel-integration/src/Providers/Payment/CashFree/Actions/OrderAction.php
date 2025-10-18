<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree\Actions;

use Closure;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support\CashFreeOrderResponse;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support\OrderWrapper;
use Mintreu\LaravelIntegration\Support\ProviderOrder;

class OrderAction
{

    protected CashFreePaymentProvider $provider;

    /**
     * @param CashFreePaymentProvider $provider
     */
    public function __construct(CashFreePaymentProvider $provider)
    {
        $this->provider = $provider;
    }



    public function create(ProviderOrder|array|Closure $data): array
    {
        if ($data instanceof Closure) {
            $order = new ProviderOrder();
            $data($order); // execute closure, fill order
            $data = $order;
        }

        $data = OrderWrapper::make($data)->toArray();



        $response =  $this->provider->getApi()->post('orders', $data);
        return CashFreeOrderResponse::make($this->provider)->capture($response,$data)
            ->getOrderResponse();
    }




    public function fetch(string $id): array
    {
        return  $this->provider->getApi()->get('orders/'.$id);
    }


    public function all(array $data = []): mixed
    {
        return $this->provider->getApi()->order->all($data);
    }




}
