<?php

namespace App\Services\Ecommerce\OrderService;

use App\Models\Ecommerce\Order;
use App\Models\Transaction;

class OrderValidationService
{

    protected Transaction $transaction;
    protected Order $order;

    /**
     * @param Transaction $transaction
     * @param Order $order
     */
    public function __construct(Transaction $transaction, Order $order)
    {
        $this->transaction = $transaction;
        $this->order = $order;
    }


    public static function make(\App\Models\Transaction $transaction, Order $order)
    {
        return new static($transaction,$order);
    }

    public function validate()
    {
    }


}
