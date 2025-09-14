<?php

namespace App\Providers;

use App\Events\User\UserNetworkSlotRequestedEvent;
use App\Listeners\User\FindAvailableNetworkSlotForUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        // You can define explicit events here if needed
        UserNetworkSlotRequestedEvent::class => [
            FindAvailableNetworkSlotForUser::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
