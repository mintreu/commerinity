<?php

declare(strict_types=1);

namespace Database\Seeders\Geo;

use App\Models\Geo\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/private/data/geo/countries.json');
        $allowed = collect(config('geo.allowed_countries', ['IN']))
            ->map(fn ($code) => strtoupper((string) $code))
            ->filter()
            ->values()
            ->all();

        if (! File::exists($jsonPath)) {
            $this->command->warn('Countries JSON file not found. Creating India as fallback.');
            $this->createIndiaFallback();

            return;
        }

        $countries = json_decode(File::get($jsonPath), true);

        if (! is_array($countries)) {
            $this->command->error('Invalid countries JSON format.');

            return;
        }

        if (! empty($allowed)) {
            $countries = array_values(array_filter(
                $countries,
                fn ($country) => in_array(strtoupper($country['iso_code_2'] ?? ''), $allowed, true)
            ));
        }

        $this->command->info('Seeding countries...');
        $bar = $this->command->getOutput()->createProgressBar(count($countries));
        $bar->start();

        foreach ($countries as $countryData) {
            Country::query()->updateOrCreate(
                ['iso_code_2' => $countryData['iso_code_2']],
                [
                    'name' => $countryData['name'],
                    'iso_code_3' => $countryData['iso_code_3'],
                    'isd_code' => $countryData['isd_code'],
                    'address_format' => $countryData['address_format'] ?? '',
                    'postcode_required' => (bool) ($countryData['postcode_required'] ?? true),
                    'locale' => 'en',
                    'region' => $countryData['region'] ?? 'Unknown',
                    'timezone' => $countryData['timezone'] ?? 'UTC',
                    'timezone_diff' => $countryData['timezone_diff'] ?? '+00:00',
                    'currency' => strtoupper($countryData['currency'] ?? 'USD'),
                    'flag' => $countryData['flag'] ?? null,
                    'exchange_rate' => null,
                    'multiplier' => 1.0,
                    'is_active' => empty($allowed)
                        ? (bool) ($countryData['status'] ?? false)
                        : in_array(strtoupper($countryData['iso_code_2'] ?? ''), $allowed, true),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Countries seeded successfully.');
    }

    /**
     * Create India as fallback if JSON not found.
     */
    private function createIndiaFallback(): void
    {
        Country::query()->updateOrCreate(
            ['iso_code_2' => 'IN'],
            [
                'name' => 'India',
                'iso_code_3' => 'IND',
                'isd_code' => 91,
                'address_format' => '{address_1}, {city}, {state_code} {postal_code}',
                'postcode_required' => true,
                'locale' => 'en',
                'region' => 'Asia',
                'timezone' => 'Asia/Kolkata',
                'timezone_diff' => '+05:30',
                'currency' => 'INR',
                'flag' => '🇮🇳',
                'exchange_rate' => ['USD' => 83.12, 'EUR' => 90.45],
                'multiplier' => 1.0,
                'is_active' => true,
            ]
        );

        $this->command->info('India created as fallback.');
    }
}
