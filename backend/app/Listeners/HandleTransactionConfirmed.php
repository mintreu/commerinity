<?php

namespace App\Listeners;


use App\Models\Lifecycle\UserSubscription;
use App\Models\Order\Order;
use App\Services\OrderService\OrderConfirmService;
use App\Services\RecruitmentService\RecruitmentConfirmationService;
use App\Services\UserServices\MembershipSubscriptionService;
use Mintreu\LaravelNaukriManager\Models\NaukriApplication;
use Mintreu\LaravelTransaction\Events\TransactionConfirmed;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

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

        if ($transactionAbleRecord instanceof Order)
        {
            $orderConfirmService = OrderConfirmService::make($transactionAbleRecord,$transaction);
            $orderConfirmService->confirm();

        }

        if ($transactionAbleRecord instanceof NaukriApplication)
        {
            RecruitmentConfirmationService::make($transactionAbleRecord)->validate($transaction);
        }

        // Wallet
        if ($transactionAbleRecord instanceof Wallet)
        {
            WalletService::make($transactionAbleRecord)->validate($transaction);
        }


    }
}
