<?php

namespace Mintreu\LaravelCommerinity\Commands;

use Illuminate\Console\Command;

class LaravelCommerinityCommand extends Command
{
    public $signature = 'laravel-commerinity';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
