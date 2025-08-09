<?php

namespace Mintreu\LaravelGeokit\Filament\CountryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelGeokit\Filament\CountryResource;

class ViewCountry extends ViewRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
