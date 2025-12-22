<?php

namespace App\Services\LifeCycleServices;

use App\Models\User;
use App\Services\LifeCycleServices\Support\LifeCycleSubscriptionService;
use Illuminate\Database\Eloquent\Model;

class LifeCycleService
{

    protected User|Model $user;

    /**
     * @param User|Model $user
     */
    public function __construct(Model|User $user)
    {
        $this->user = $user;
    }


    public static function make(Model|User $user)
    {
        return new static($user);
    }


    public function subscription(): LifeCycleSubscriptionService
    {
        return new LifeCycleSubscriptionService($this->user);
    }










}
