<?php

namespace Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource;

class ViewState extends ViewRecord
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
