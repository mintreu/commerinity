<?php

namespace App\Listeners\User;

use App\Events\User\UserBusinessCheckEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleUserBusiness
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
    public function handle(UserBusinessCheckEvent $event): void
    {
        //
    }
}
