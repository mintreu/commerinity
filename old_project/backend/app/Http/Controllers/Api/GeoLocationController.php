<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Geo\CountryIndexResource;
use App\Http\Resources\Geo\CountryResource;
use App\Http\Resources\Geo\StateResource;
use Illuminate\Http\Request;
use Mintreu\LaravelGeokit\Models\Country;
use Mintreu\LaravelGeokit\Models\State;

class GeoLocationController extends Controller
{

    public function getAllCountries(Request $request)
    {
        $countries = Country::select([
            'id',
            'name',
            'iso_code_2',
            'isd_code',
            'locale',
            'timezone',
            'currency',
            'flag',
            'exchange_rate',
            'multiplier',
            'is_active',
        ])
            ->where('is_active',true)
            ->orderBy('name')->get();
        return CountryIndexResource::collection($countries);
    }


    public function getCountry(Request $request,Country $country)
    {
        $country->load('states');

        return CountryResource::make($country);
    }

    public function getAllStatesOfACountry(Country $country,Request $request)
    {
        $country->load('states');
        return StateResource::collection($country->states);
    }

    public function getState(State $state,Request $request)
    {
        $state->load('blocks');
        return StateResource::make($state);
    }



}
