<?php

namespace Mintreu\Toolkit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintreu\Toolkit\Toolkit
 */
class Toolkit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mintreu\Toolkit\Toolkit::class;
    }
}
