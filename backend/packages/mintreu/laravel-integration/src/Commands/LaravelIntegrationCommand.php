<?php

namespace Mintreu\LaravelIntegration\Commands;

use Illuminate\Console\Command;
use Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry;

class LaravelIntegrationCommand extends Command
{
    public $signature = 'laravel-integration';

    public $description = 'My command';

    public function handle(): int
    {
        LaravelIntegrationRegistry::make()->reset();

        return self::SUCCESS;
    }
}
