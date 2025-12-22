<?php

namespace Mintreu\LaravelRecruitment\Commands;

use Illuminate\Console\Command;

class LaravelRecruitmentCommand extends Command
{
    public $signature = 'laravel-recruitment';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
