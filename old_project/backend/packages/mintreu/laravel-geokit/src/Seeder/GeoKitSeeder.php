<?php

namespace Mintreu\LaravelGeokit\Seeder;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Models\Country;

class GeoKitSeeder extends Seeder
{

    public static function make():static
    {
        return new static();
    }




    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allowedCountries = config('laravel-geokit.seeder.countries');

        foreach ($allowedCountries as $countryIsoCode2)
        {
            $countryData = Storage::disk(config('laravel-geokit.storage.disk','local'))->get(config('laravel-geokit.storage.path').$countryIsoCode2.'.json');

            $this->seedNow(json_decode($countryData));

        }


    }







    protected function seedNow(object $countryData): void
    {
        $statesData = $countryData->states ?? null;

        if (is_null($statesData) || !is_iterable($statesData)) {
            Log::warning('No states found for country', ['country' => $countryData]);
            return;
        }

        // Clone and strip non-model properties
        $countryAttributes = clone $countryData;
        unset($countryAttributes->states);

        try {
            DB::transaction(function () use ($countryAttributes, $statesData) {
                $country = Country::updateOrCreate(
                    [
                        'iso_code_2' => $countryAttributes->iso_code_2,
                        'iso_code_3' => $countryAttributes->iso_code_3,
                        'is_active'  => in_array($countryAttributes->iso_code_2,config('laravel-geokit.seeder.countries'))
                    ],
                    (array) $countryAttributes
                );

                foreach ($statesData as $stateData) {
                    $stateCities = $stateData->cities ?? null;

                    if (is_null($stateCities) || !is_iterable($stateCities)) {
                        Log::warning('No cities found for state', ['state' => $stateData]);
                        continue;
                    }

                    $stateAttributes = clone $stateData;
                    unset($stateAttributes->cities, $stateAttributes->latitude, $stateAttributes->longitude);

                    $newState = $country->states()->updateOrCreate(
                        [
                            'code' => $stateAttributes->code,
                        ],
                        (array) $stateAttributes
                    );

                    foreach ($stateCities as $block) {
                        if (
                            !isset($block->name, $block->district_name, $block->latitude, $block->longitude)
                        ) {
                            Log::error('Missing block data', ['block' => $block]);
                            continue;
                        }

                        $name = ucwords(Str::lower($block->name));
                        $newState->blocks()->create([
                            'name' => $name,
                            'url' => Str::slug($name),
                            'district_name' => ucwords(Str::lower($block->district_name)),
                            'latitude' => $block->latitude,
                            'longitude' => $block->longitude,
                        ]);
                    }
                }
            });

            Log::info('Seeding completed successfully', ['country' => $countryAttributes->iso_code_2]);

        } catch (\Throwable $e) {
            Log::error('Seeding failed', [
                'country' => $countryData->iso_code_2 ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }









}
