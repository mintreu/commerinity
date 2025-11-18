<?php

namespace App\Services\NetworkBusinessService;

use App\Models\User;

class NetworkBusinessService
{

    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public static function make(User $user)
    {
        return new static($user);
    }

    public function calculateForUser()
    {
        // 1. Calculate this user’s personal volume (PV)
        $pv = $this->calculatePV();

        // 2. Propagate PV to all ancestors
        $parent = $this->user->parent;

        while ($parent) {
            $parent->group_volume += $pv;
            $parent->save();

            $parent = $parent->parent;
        }

        // 3. Optionally recalc rank of this user
        $this->calculateRank();
    }

    protected function calculatePV()
    {
        return $this->user->orders()->sum('amount'); // example
    }

    protected function calculateRank()
    {
        // simple example
        if ($this->user->group_volume > 10000) {
            $this->user->rank = 'Gold';
            $this->user->save();
        }
    }



}
