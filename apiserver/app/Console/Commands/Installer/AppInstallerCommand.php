<?php

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
    protected $description = 'Run the full application installation process.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->comment('-------------------------------------------');
        $this->comment('🚀 Starting application installation process');
        $this->comment('-------------------------------------------');
        
        $this->info('Generating application key...');
        $this->call('key:generate');

        $this->warn('Running fresh migrations (all data will be lost)...');
        $this->call('migrate:fresh');

        $this->comment('Clearing caches and optimizations...');
        $this->call('optimize:clear');

        $this->comment('Linking storage...');
        $result = $this->call('storage:link');

        if ($result === 0) {
            $this->info('✔ Storage linked successfully.');
        } else {
            $this->error('✖ Failed to link storage.');
        }

        $this->comment('Cleaning up public storage leftovers...');
        $this->clearPublicLeftOvers();
        sleep(2);
        $this->info('✔ Public storage cleaned (except .gitignore).');


        $this->info('Seeding database with default data...');
        $this->call('laravel-geokit:seed');
        $this->call('db:seed');

        $this->info('Caching system configurations and optimizing system');
        $this->call('optimize');
        $this->info('System now cached and optimized');

        $this->info('🎉 Application installed successfully!');
    }

    /**
     * Remove all files/folders from storage/app/public except .gitignore.
     */
    protected function clearPublicLeftOvers(): void
    {
        $directory = storage_path('app/public');

        if (!File::exists($directory)) {
            $this->warn('⚠ storage/app/public directory does not exist.');
            return;
        }

        // Delete all files except .gitignore
        foreach (File::allFiles($directory) as $file) {
            if ($file->getFilename() !== '.gitignore') {
                File::delete($file->getRealPath());
            }
        }

        // Delete all subdirectories
        foreach (File::directories($directory) as $dir) {
            File::deleteDirectory($dir);
        }
    }
}
