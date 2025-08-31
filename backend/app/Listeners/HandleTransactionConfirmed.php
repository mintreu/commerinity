<?php

namespace App\Listeners;


use App\Models\Lifecycle\UserSubscription;
use App\Services\UserServices\MembershipSubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mintreu\LaravelTransaction\Events\TransactionConfirmed;

class HandleTransactionConfirmed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionConfirmed $event): void
    {
        $transaction = $event->getTransaction();
        $transaction->load('transactionable.customer');

        $transactionAbleRecord = $transaction->transactionable;
        $customer = $transactionAbleRecord->customer;
        if ($transactionAbleRecord instanceof UserSubscription)
        {
            MembershipSubscriptionService::make($customer)->validate($transaction);
        }

    }
}
