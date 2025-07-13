<?php

namespace Mintreu\LaravelNaukriManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelNaukriManager\LaravelNaukriManager
 */
class LaravelNaukriManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelNaukriManager\LaravelNaukriManager::class;
    }
}
