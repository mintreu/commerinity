<?php

namespace Mintreu\LaravelGeokit\Filament\StateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelGeokit\Filament\StateResource;

class ViewState extends ViewRecord
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
