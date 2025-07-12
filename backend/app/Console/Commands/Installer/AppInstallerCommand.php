<?php

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;

class AppInstallerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

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
        $this->info('Installing application...');
        $this->call('key:generate');
        $this->call('migrate:fresh');
        $this->call('db:seed');
        //$this->call('shield:install');
        $this->call('optimize:clear');
        $result = $this->call('storage:link');
        $this->info((string) $result);
        $this->info('Application installed successfully.');
    }
}
