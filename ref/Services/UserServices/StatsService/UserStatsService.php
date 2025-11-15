<?php

namespace App\Services\UserServices\StatsService;

use App\Models\User;
use App\Services\UserServices\StatsService\Support\UserEarningsService;
use App\Services\UserServices\StatsService\Support\UserReferralStatService;
use Illuminate\Database\Eloquent\Model;

class UserStatsService
{

    protected Model|User $record;

    /**
     * @param User|Model $record
     */
    public function __construct(Model|User $record)
    {
        $this->record = $record;
    }


    public static function make(Model|User $record):static
    {
        return new static($record);
    }


    public function earning(): UserEarningsService
    {
        return new UserEarningsService($this->record);
    }


    public function affiliate(): UserReferralStatService
    {
        return new UserReferralStatService($this->record);
    }



}
