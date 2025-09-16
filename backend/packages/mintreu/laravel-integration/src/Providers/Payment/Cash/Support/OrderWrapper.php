<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Cash\Support;

use Mintreu\LaravelIntegration\Support\ProviderOrder;

class OrderWrapper
{

    protected ProviderOrder|array $orderData;


    /**
     * @param array|ProviderOrder $data
     */
    public function __construct(ProviderOrder|array $data)
    {
        $this->orderData = $data;
    }


    public static function make(\Mintreu\LaravelIntegration\Support\ProviderOrder|array $data)
    {
        return new static($data);
    }



    public function toArray()
    {
        return $this->orderData instanceof ProviderOrder ? $this->orderSchema() : $this->orderData;
    }

    private function orderSchema(): array
    {
        return [
            'currency' => $this->orderData->getCurrency(),
            'amount'   => $this->orderData->getAmount(),
            'receipt'  => $this->orderData->getReceipt(),
            'notes'    => $this->orderData->getNotes()
        ];
    }

}
