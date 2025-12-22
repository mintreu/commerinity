<?php

namespace Mintreu\LaravelTransaction\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mintreu\LaravelTransaction\Models\Transaction;

class TransactionFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
