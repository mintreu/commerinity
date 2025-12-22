<?php

namespace App\Console\Commands\BusinessManager;

use App\Services\NetworkBusinessService\UserProgressChecker;
use Illuminate\Console\Command;

class BusinessManagerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:business-recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Business Auto Calculation Command Running');

        UserProgressChecker::init();




        $this->info('Business Auto Calculation Command Finished');
    }
}
