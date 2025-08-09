<?php

namespace Mintreu\LaravelGeokit\Commands\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasSeedingData
{

    private function getPath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }

    private function getFeedPath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR.'feed'.DIRECTORY_SEPARATOR;
    }

    public function getStoredPath(): string
    {
        return config('laravel-geokit.storage.path');
    }


    public function ensureSeederDataAvailable(): void
    {
        $allowedCountries = config('laravel-geokit.seeder.countries');
        $hasAll = true;

        foreach ($allowedCountries as $countryIsoCode2) {
            if (!Storage::disk(config('laravel-geokit.storage.disk','local'))->exists($this->getStoredPath().$countryIsoCode2.'.json')) {
                $hasAll = false;
                break; // No need to check further, one is missing
            }
        }

        if (!$hasAll) {
            $this->prepareCountriesData($allowedCountries);
        }
    }




    private function prepareCountriesData(array $allowedCountries): void
    {
        $countriesData = [];

        // Load data from the original countries.json file
        $allCountries = json_decode(file_get_contents($this->getFeedPath().'countries.json'));


        foreach ($allCountries as $country) {
            if (in_array($country->iso_code_2, $allowedCountries)) {
                $countriesData [$country->iso_code_2] = [
                    'name' => $country->name,
                    'iso_code_2' => $country->iso_code_2,
                    'iso_code_3' => $country->iso_code_3,
                    'isd_code' => $country->isd_code,
                    'address_format' => $country->address_format ?? '',
                    'postcode_required' => $country->postcode_required ?? false,
                    'region' => $country->region ?? '',
                    'timezone' => $country->timezone ?? '',
                    'timezone_diff' => $country->timezone_diff ?? '',
                    'currency' => strtoupper($country->currency ?? ''),
                    'flag' => $country->flag ?? '',
                ];

                // Fetch states for the allowed country from world.json
                $countriesData[$country->iso_code_2]['states'] = $this->fetchStatesFromWorldJson($country->iso_code_2);
            }
        }


        if ($countriesData)
        {
            foreach ($countriesData as $key => $data)
            {
                Storage::disk(config('laravel-geokit.storage.disk','local'))->put($this->getStoredPath().$key.'.json',json_encode($data));
            }
        }

    }





    protected function fetchStatesFromWorldJson($countryIso): array
    {
        $states = [];
        $worldData = json_decode(file_get_contents($this->getFeedPath().'world.json'));
        $blockData = [];
        if (file_exists($this->getFeedPath().$countryIso.DIRECTORY_SEPARATOR.'blocks.json'))
        {
            $blockData = json_decode(file_get_contents($this->getFeedPath().$countryIso.DIRECTORY_SEPARATOR.'blocks.json'));
        }else{
            // Fallback to default IN
            $blockData = json_decode(file_get_contents($this->getFeedPath().'IN'.DIRECTORY_SEPARATOR.'blocks.json'));
        }


        foreach ($worldData as $worldCountry) {
            if ($worldCountry->iso2 === $countryIso) {
                foreach ($worldCountry->states as $state) {
                    $cities = [];

                    foreach ($state->cities as $city)
                    {
                        foreach ($blockData as $district)
                        {
                            foreach ($district->blockList as $block)
                            {
                                $searchIn = [Str::lower($state->name),Str::lower($city->name)];
                                $blockPart = explode('-',$block->name);
                                $hasPart = isset($blockPart[0]) && in_array(Str::lower($blockPart[0]),$searchIn);
                                if (in_array(Str::lower($block->name),$searchIn) || in_array(Str::lower($district->name),$searchIn) || $hasPart){

                                    $cities[] = [
                                        'name' => $block->name,
                                        'latitude' => $city->latitude,
                                        'longitude' => $city->longitude,
                                        'state_code' => $state->state_code,
                                        'district_name' => $district->name
                                    ];
                                }
                            }
                        }
                    }


                    $states[] = [
                        'name' => $state->name,
                        'code' => $state->state_code,
                        'latitude' => $state->latitude,
                        'longitude' => $state->longitude,
                        'cities' => $cities
                    ];
                }
                break;
            }
        }

        return $states;
    }



}
