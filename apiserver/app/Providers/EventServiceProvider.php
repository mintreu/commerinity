<?php

namespace App\Providers;

use App\Events\PaymentCompleted;
use App\Events\Affiliate\CommissionProcessed;
use App\Events\Affiliate\SubscriptionActivated;
use App\Listeners\Payment\HandlePaymentCompleted;
use App\Listeners\Notification\SendCommissionProcessedNotification;
use App\Listeners\Notification\SendSubscriptionActivatedNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCompleted::class => [
            HandlePaymentCompleted::class,
        ],
        SubscriptionActivated::class => [
            SendSubscriptionActivatedNotifications::class,
        ],
        CommissionProcessed::class => [
            SendCommissionProcessedNotification::class,
        ],
    ];
}
