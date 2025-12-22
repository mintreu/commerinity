<?php

namespace Mintreu\LaravelGeokit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Mintreu\LaravelGeokit\Commands\Traits\HasSeedingData;
use Mintreu\LaravelGeokit\Seeder\GeoKitSeeder;
use Throwable;

/**
 * Seed geolocation data (countries, states, cities) into the database.
 *
 * Usage:
 *   php artisan laravel-geokit:seed
 *   php artisan laravel-geokit:seed --fresh
 *   php artisan laravel-geokit:seed --country=IN
 */
class LaravelGeokitSeedingCommand extends Command
{
    use HasSeedingData;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-geokit:seed
                            {--country= : Seed only the specified country (ISO 2 code)}
                            {--fresh : Overwrite existing JSON cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed geolocation data (countries, states, cities) into the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌍 Starting geolocation data seeding...');

        $country = $this->option('country');
        $fresh = $this->option('fresh');

        // Determine which countries to seed
        $countries = $country ? [$country] : config('laravel-geokit.seeder.countries', []);

        if (empty($countries)) {
            $this->error('No countries configured for seeding.');
            return self::FAILURE;
        }

        // Prepare JSON data if needed
        $this->prepareSeederData($countries, $fresh);

        try {
            $this->info('📥 Running GeoKit Seeder...');
            GeoKitSeeder::make()->run($countries);
            $this->info('✅ Geolocation data seeded successfully.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    /**
     * Prepare the JSON files for the given countries.
     */
    protected function prepareSeederData(array $countries, bool $fresh): void
    {
        $disk = config('laravel-geokit.storage.disk', 'local');
        $path = $this->getStoredPath();

        foreach ($countries as $code) {
            $file = $path . $code . '.json';

            if ($fresh && Storage::disk($disk)->exists($file)) {
                Storage::disk($disk)->delete($file);
                $this->warn("⚠️  Removed cached file for {$code}");
            }
        }

        $this->ensureSeederDataAvailable();
        $this->info("📦 JSON data loaded from: {$path}");
    }
}
