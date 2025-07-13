<?php

namespace Mintreu\LaravelNaukriManager\Commands;

use Illuminate\Console\Command;

class LaravelNaukriManagerCommand extends Command
{
    public $signature = 'laravel-naukri-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
