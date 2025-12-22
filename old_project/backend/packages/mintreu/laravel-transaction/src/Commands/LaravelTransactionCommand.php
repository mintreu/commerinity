<?php

namespace Mintreu\LaravelTransaction\Commands;

use Illuminate\Console\Command;

class LaravelTransactionCommand extends Command
{
    public $signature = 'laravel-transaction';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
