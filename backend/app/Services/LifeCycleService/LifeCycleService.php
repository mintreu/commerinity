<?php

namespace App\Services\LifeCycleService;

class LifeCycleService
{








    public static function make(\Illuminate\Database\Eloquent\Model $record)
    {
        return new static($record);
    }
}
