<?php

namespace Mintreu\LaravelPenpress\Commands;

use Illuminate\Console\Command;

class LaravelPenpressCommand extends Command
{
    public $signature = 'laravel-penpress';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
