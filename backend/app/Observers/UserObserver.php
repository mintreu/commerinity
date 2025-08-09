<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\GoodbyeNotification;
use App\Notifications\WelcomeNotification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Send welcome notification for users created with verified email (social login)
        if ($user->hasVerifiedEmail() || ! is_null($user->mobile_verified_at)) {
            $user->notify(new WelcomeNotification);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->notify(new GoodbyeNotification);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
