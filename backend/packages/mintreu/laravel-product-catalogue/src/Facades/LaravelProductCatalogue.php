<?php

namespace Mintreu\LaravelProductCatalogue\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelProductCatalogue\LaravelProductCatalogue
 */
class LaravelProductCatalogue extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelProductCatalogue\LaravelProductCatalogue::class;
    }
}
