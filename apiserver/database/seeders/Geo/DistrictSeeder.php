<?php

declare(strict_types=1);

namespace Database\Seeders\Geo;

use App\Models\Geo\District;
use App\Models\Geo\State;
use App\Support\Geo\DistrictNameMapper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = storage_path('app/private/data/geo/india.json');

        if (! File::exists($jsonPath)) {
            $this->command->warn('India JSON file not found. Skipping district seeding.');

            return;
        }

        $indiaData = json_decode(File::get($jsonPath), true);
        if (! isset($indiaData['states']) || ! is_array($indiaData['states'])) {
            $this->command->error('Invalid India JSON format or no states found.');

            return;
        }

        $stateMap = State::query()->get(['id', 'code'])->keyBy('code');
        $districtsByState = $this->extractDistrictsByState($indiaData['states']);

        $seedAllIndia = $this->isAllIndiaDatasetReliable($districtsByState);

        if ($seedAllIndia) {
            $this->seedAllIndiaDistricts($districtsByState, $stateMap);
            $this->command->info('District seeding completed for all states.');

            return;
        }

        $this->seedWestBengalMinimum($stateMap);
        $this->command->warn('District JSON quality is mixed. Seeded canonical West Bengal districts only.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $statesData
     * @return array<string, array<int, string>>
     */
    private function extractDistrictsByState(array $statesData): array
    {
        $districtsByState = [];

        foreach ($statesData as $stateData) {
            $stateCode = strtoupper((string) ($stateData['code'] ?? ''));
            if ($stateCode === '') {
                continue;
            }

            $districts = collect((array) ($stateData['cities'] ?? []))
                ->pluck('district_name')
                ->filter(fn ($name) => filled($name))
                ->map(fn ($name) => trim((string) $name))
                ->unique()
                ->values()
                ->all();

            $districtsByState[$stateCode] = $districts;
        }

        return $districtsByState;
    }

    /**
     * @param  array<string, array<int, string>>  $districtsByState
     */
    private function isAllIndiaDatasetReliable(array $districtsByState): bool
    {
        foreach ($districtsByState as $districts) {
            if (count($districts) === 0) {
                return false;
            }
        }

        $westBengalDistricts = $districtsByState['WB'] ?? [];
        if (! DistrictNameMapper::isReliableWestBengalDataset($westBengalDistricts)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, array<int, string>>  $districtsByState
     * @param  \Illuminate\Support\Collection<string, \App\Models\Geo\State>  $stateMap
     */
    private function seedAllIndiaDistricts(array $districtsByState, $stateMap): void
    {
        foreach ($districtsByState as $stateCode => $districts) {
            $state = $stateMap->get($stateCode);
            if (! $state) {
                continue;
            }

            foreach ($districts as $rawName) {
                $name = DistrictNameMapper::canonicalize($stateCode, $rawName)
                    ?? DistrictNameMapper::toDisplayName($rawName);

                District::query()->updateOrCreate(
                    [
                        'state_id' => $state->id,
                        'name' => $name,
                    ],
                    [
                        'slug' => Str::slug($name),
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \App\Models\Geo\State>  $stateMap
     */
    private function seedWestBengalMinimum($stateMap): void
    {
        $westBengal = $stateMap->get('WB');
        if (! $westBengal) {
            $this->command->warn('WB state not found. Skipping district seeding.');

            return;
        }

        foreach (DistrictNameMapper::westBengalDistricts() as $district) {
            District::query()->updateOrCreate(
                [
                    'state_id' => $westBengal->id,
                    'name' => $district,
                ],
                [
                    'slug' => Str::slug($district),
                    'is_active' => true,
                ],
            );
        }
    }
}

