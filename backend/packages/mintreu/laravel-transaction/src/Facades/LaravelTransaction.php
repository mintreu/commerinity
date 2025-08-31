<?php

namespace Mintreu\LaravelTransaction\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelTransaction\LaravelTransaction
 */
class LaravelTransaction extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelTransaction\LaravelTransaction::class;
    }
}
