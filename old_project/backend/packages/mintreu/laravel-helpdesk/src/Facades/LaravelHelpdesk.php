<?php

namespace Mintreu\LaravelHelpdesk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\LaravelHelpdesk\LaravelHelpdesk
 */
class LaravelHelpdesk extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintreu\LaravelHelpdesk\LaravelHelpdesk::class;
    }
}
