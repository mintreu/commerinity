<?php

namespace Mintreu\LaravelPenpress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelPenpress\LaravelPenpress
 */
class LaravelPenpress extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelPenpress\LaravelPenpress::class;
    }
}
