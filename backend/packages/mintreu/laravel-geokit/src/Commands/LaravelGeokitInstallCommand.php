<?php

namespace Mintreu\LaravelGeokit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Class LaravelGeokitInstallCommand
 *
 * This command sets up the LaravelGeokit package in a Laravel project.
 * It will:
 *   - Publish migration files from the package
 *   - Run database migrations
 *   - Seed geolocation data using the `laravel-geokit:seed` command
 *
 * Usage:
 *   php artisan laravel-geokit:install
 */
class LaravelGeokitInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-geokit:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish migration files, run migrations, and seed geolocation data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Installing LaravelGeokit package...');

        // Step 1: Publish migration files
        $this->call('vendor:publish', [
            '--tag' => 'laravel-geokit-migrations',
            '--force' => true,
        ]);
        $this->info('✅ Migrations published successfully.');

        // Step 2: Run migrations
        $this->call('migrate');
        $this->info('✅ Migrations completed successfully.');

        // Step 3: Seed geolocation data
        $this->call('laravel-geokit:seed');
        $this->info('✅ Seeding complete.');

        $this->comment('🎉 LaravelGeokit installation finished successfully.');

        return self::SUCCESS;
    }
}
