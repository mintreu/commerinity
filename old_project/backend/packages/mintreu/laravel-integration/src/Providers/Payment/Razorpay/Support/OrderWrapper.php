<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Support;

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

//    private function orderSchema(): array
//    {
//        return [
//            'currency' => $this->orderData->getCurrency(),
//            'amount'   => $this->orderData->getAmount(),
//            'receipt'  => $this->orderData->getReceipt(),
//            'notes'    => $this->orderData->getNotes()
//        ];
//    }



    private function orderSchema(): array
    {
        $amount = $this->orderData->getAmount();

        // agar float/decimal hua toh round karke integer banao
        if (!is_int($amount)) {
            $amount = (int) round($amount * 100); // Razorpay ke liye paise/cents
        }

        return [
            'currency' => $this->orderData->getCurrency(),
            'amount'   => $amount,
            'receipt'  => $this->orderData->getReceipt(),
            'notes'    => $this->orderData->getNotes(),
        ];
    }




}
