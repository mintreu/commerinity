<?php

namespace Mintreu\LaravelCatalogueManager\Commands;

use Illuminate\Console\Command;

class LaravelCatalogueManagerCommand extends Command
{
    public $signature = 'laravel-catalogue-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
