<?php

namespace App\Providers;

use App\Events\PaymentCompleted;
use App\Listeners\Payment\HandlePaymentCompleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCompleted::class => [
            HandlePaymentCompleted::class,
        ],
    ];
}
