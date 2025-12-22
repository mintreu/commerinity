<?php

namespace Mintreu\LaravelCommerinity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelCommerinity\LaravelCommerinity
 */
class LaravelCommerinity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelCommerinity\LaravelCommerinity::class;
    }
}
