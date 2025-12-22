<?php

namespace Mintreu\LaravelProductCatalogue\Commands;

use Illuminate\Console\Command;

class LaravelProductCatalogueCommand extends Command
{
    public $signature = 'laravel-product-catalogue';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
