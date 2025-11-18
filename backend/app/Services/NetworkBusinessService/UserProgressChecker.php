<?php

namespace App\Services\NetworkBusinessService;

use App\Models\Lifecycle\Level;
use App\Models\User;

class UserProgressChecker
{

    public static function init()
    {
        $levels = Level::where('status', true)->orderBy('id')->get();

        foreach ($levels as $level) {
            User::where('level_id', $level->id)
                ->orderBy('id')
                ->chunkById(500, function ($users) {
                    foreach ($users as $user) {
                        NetworkBusinessService::make($user)->calculateForUser();
                    }
                });
        }


    }

}
