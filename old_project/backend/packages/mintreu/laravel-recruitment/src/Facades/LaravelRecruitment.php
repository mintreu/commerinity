<?php

namespace Mintreu\LaravelRecruitment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelRecruitment\LaravelRecruitment
 */
class LaravelRecruitment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelRecruitment\LaravelRecruitment::class;
    }
}
