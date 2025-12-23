<?php

namespace App\Filament\Resources\Geo\Countries\Pages;

use App\Filament\Resources\Geo\Countries\CountryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;
}
