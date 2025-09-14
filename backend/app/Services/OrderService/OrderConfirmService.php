<?php

namespace App\Services\OrderService;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use Mintreu\LaravelTransaction\Models\Transaction;

class OrderConfirmService
{

    protected Order $order;
    protected \Mintreu\LaravelTransaction\Models\Transaction $transaction;

    /**
     * @param Order $order
     * @param Transaction|null $transaction
     */
    public function __construct(Order $order,?\Mintreu\LaravelTransaction\Models\Transaction $transaction = null)
    {
        $this->order = $order;
        if (is_null($transaction))
        {
            $this->order->load('transaction');
        }
        $this->transaction = $transaction ?? $this->order->transaction;
    }



    public static function make(Order $order,?\Mintreu\LaravelTransaction\Models\Transaction $transaction = null)
    {
        return new static($order,$transaction);
    }


    public function confirm()
    {


        $this->order->update([
           'status'     => OrderStatusCast::CONFIRM
        ]);

    }



}
