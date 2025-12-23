<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;

class UserBannedException extends Exception
{
    public function __construct(string $message = 'Account is banned')
    {
        parent::__construct($message, 403);
    }
}
