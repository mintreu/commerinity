<?php

declare(strict_types=1);

namespace Database\Seeders\Geo;

use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/private/data/geo/india.json');

        if (! File::exists($jsonPath)) {
            $this->command->warn('India JSON file not found. Skipping states seeding.');

            return;
        }

        $indiaData = json_decode(File::get($jsonPath), true);

        if (! isset($indiaData['states']) || ! is_array($indiaData['states'])) {
            $this->command->error('Invalid India JSON format or no states found.');

            return;
        }

        $india = Country::query()->where('iso_code_2', 'IN')->first();

        if (! $india) {
            $this->command->error('India country not found. Please seed countries first.');

            return;
        }

        $this->command->info('Seeding Indian states...');
        $bar = $this->command->getOutput()->createProgressBar(count($indiaData['states']));
        $bar->start();

        foreach ($indiaData['states'] as $stateData) {
            State::query()->updateOrCreate(
                [
                    'code' => $stateData['code'],
                    'country_id' => $india->id,
                ],
                [
                    'name' => $stateData['name'],
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Indian states seeded successfully.');
    }
}
