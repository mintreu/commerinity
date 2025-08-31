<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support;

use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Throwable;
use function PHPUnit\Framework\throwException;

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


    public static function make(ProviderOrder|array $data)
    {
        return new static($data);
    }



    public function toArray()
    {
        return $this->orderData instanceof ProviderOrder ? $this->orderSchema() : $this->orderData;
    }

    /**
     * @throws Throwable
     */
    private function orderSchema(): array
    {
        if (is_null($this->orderData->getCustomerId()) || is_null($this->orderData->getCustomerMobile())) {
            throw new \InvalidArgumentException(
                'LaravelIntegration (CashFree):: Customer uuid and mobile info missing'
            );
        }


        return [
            'order_id' => $this->orderData->getReceipt(),
            'order_currency' => $this->orderData->getCurrency(),
            'order_amount'   => $this->orderData->getAmount(),
//            'order_expiry_time' => $this->orderData->getExpireAt()->toIso8601String(),
//            'order_expiry_time' => $this->orderData
//                ->getExpireAt()
//                ->copy()
//                ->setTimezone('Asia/Kolkata')
//                //->toIso8601String()
//                //->toAtomString()
//                ->format('Y-m-d\TH:i:sP'),

            'customer_details' => [
                'customer_id'   => $this->orderData->getCustomerId(),
                'customer_phone'=> $this->orderData->getCustomerMobile(),
            ],
            'order_meta' => [
                'return_url' => $this->orderData->getSuccessUrl(),
                'notify_url' => $this->orderData->getFailureUrl()
            ]
        ];
    }



}
