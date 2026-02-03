<?php

namespace App\Observers;

use App\Casts\WalletStatusCast;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {


        $user->wallet()->create([
            'balance' => 0,
            'hold_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'points' => rand(0, 1000),
            'pin' => Hash::make('123456'), // Demo PIN
            'pin_updated_at' => now(),
            'currency' => 'INR',
            'status' => WalletStatusCast::ACTIVE,
        ]);


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
        //
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

