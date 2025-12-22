<?php

namespace Mintreu\LaravelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelMoney\LaravelMoney
 */
class LaravelMoney extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelMoney\LaravelMoney::class;
    }
}
