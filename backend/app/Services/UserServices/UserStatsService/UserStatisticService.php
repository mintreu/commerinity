<?php

namespace App\Services\UserServices\UserStatsService;

use App\Models\User;

class UserStatisticService
{

    protected User $user;

    protected array $statistics = [];

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public static function make(User $record)
    {
        return new static($record);
    }

    public function getMinimalStats()
    {

    }


    public function getFullStats()
    {

    }









}
