<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;

class UserSuspendedException extends Exception
{
    public function __construct(string $message = 'Account is suspended')
    {
        parent::__construct($message, 403);
    }
}
