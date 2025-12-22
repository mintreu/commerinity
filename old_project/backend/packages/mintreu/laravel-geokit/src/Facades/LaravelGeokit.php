<?php

namespace Mintreu\LaravelGeokit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelGeokit\LaravelGeokit
 */
class LaravelGeokit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelGeokit\LaravelGeokit::class;
    }
}
