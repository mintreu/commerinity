<?php

namespace Mintreu\LaravelShopline\Commands;

use Illuminate\Console\Command;

class LaravelShoplineCommand extends Command
{
    public $signature = 'laravel-shopline';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
