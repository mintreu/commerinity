<?php

namespace Mintreu\LaravelCatalogueManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelCatalogueManager\LaravelCatalogueManager
 */
class LaravelCatalogueManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelCatalogueManager\LaravelCatalogueManager::class;
    }
}
