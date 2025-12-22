<?php

namespace Mintreu\LaravelTransaction;

use Illuminate\Http\Request;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Models\Transaction;

class LaravelTransaction
{

    protected Transaction $transaction;
    protected Request $request;

    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->transaction->load('integration');
    }


    public static function make(Transaction $transaction)
    {
        return new static($transaction);
    }



    public function callback(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function validate():bool
    {

       $valid = LaravelIntegration::payment($this->transaction->integration->url)->verify()
            ->viaCallBack($this->request,$this->transaction);

       if ($valid)
       {
           $this->transaction->update([
              'verified' => true,
              'status' => TransactionStatusCast::COMPLETED
           ]);
       }

       return $valid;
    }



    public function failed()
    {
    }


}
