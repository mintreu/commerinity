<?php

namespace Mintreu\LaravelCategory\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelCategory\LaravelCategory
 */
class LaravelCategory extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelCategory\LaravelCategory::class;
    }
}
