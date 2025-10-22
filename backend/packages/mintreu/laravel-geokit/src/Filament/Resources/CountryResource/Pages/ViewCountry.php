<?php

namespace Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource;

class ViewCountry extends ViewRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
