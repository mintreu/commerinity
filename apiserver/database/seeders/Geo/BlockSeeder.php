<?php

declare(strict_types=1);

namespace Database\Seeders\Geo;

use App\Models\Geo\Block;
use App\Models\Geo\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/private/data/geo/india.json');

        if (! File::exists($jsonPath)) {
            $this->command->warn('India JSON file not found. Skipping blocks seeding.');

            return;
        }

        $indiaData = json_decode(File::get($jsonPath), true);

        if (! isset($indiaData['states']) || ! is_array($indiaData['states'])) {
            $this->command->error('Invalid India JSON format or no states found.');

            return;
        }

        $this->command->info('Seeding Indian blocks/cities...');

        $totalBlocks = 0;
        foreach ($indiaData['states'] as $stateData) {
            if (isset($stateData['cities']) && is_array($stateData['cities'])) {
                $totalBlocks += count($stateData['cities']);
            }
        }

        $bar = $this->command->getOutput()->createProgressBar($totalBlocks);
        $bar->start();

        foreach ($indiaData['states'] as $stateData) {
            if (! isset($stateData['cities']) || ! is_array($stateData['cities'])) {
                continue;
            }

            $state = State::query()->where('code', $stateData['code'])->first();

            if (! $state) {
                $this->command->warn("State {$stateData['code']} not found. Skipping its blocks.");

                continue;
            }

            foreach ($stateData['cities'] as $cityData) {
                $baseUrl = Str::slug($cityData['name']);
                $url = $baseUrl;
                $counter = 1;

                // Ensure unique URL globally (across all states)
                while (Block::query()->where('url', $url)->exists()) {
                    $url = "{$baseUrl}-{$counter}";
                    $counter++;
                }

                Block::query()->updateOrCreate(
                    [
                        'name' => $cityData['name'],
                        'state_code' => $state->code,
                    ],
                    [
                        'url' => $url,
                        'district_name' => $cityData['district_name'] ?? '',
                        'latitude' => isset($cityData['latitude']) ? (float) $cityData['latitude'] : null,
                        'longitude' => isset($cityData['longitude']) ? (float) $cityData['longitude'] : null,
                    ]
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Indian blocks/cities seeded successfully.');
    }
}
