<?php

namespace Mintreu\LaravelShopline\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelShopline\LaravelShopline
 */
class LaravelShopline extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelShopline\LaravelShopline::class;
    }
}
