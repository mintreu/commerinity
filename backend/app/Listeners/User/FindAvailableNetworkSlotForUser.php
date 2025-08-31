<?php

namespace App\Listeners\User;

use App\Events\User\UserNetworkSlotRequestedEvent;
use App\Models\User;
use App\Services\UserServices\NetworkServices\NetworkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FindAvailableNetworkSlotForUser
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
    public function handle(UserNetworkSlotRequestedEvent $event): void
    {
        $user = $event->getRecord();
        $networkService = NetworkService::make($user);
        $networkService->addToNetwork($user,$user->parent()?->first());


    }
}
