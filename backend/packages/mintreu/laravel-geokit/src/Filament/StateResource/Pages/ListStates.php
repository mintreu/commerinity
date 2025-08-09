<?php

namespace Mintreu\LaravelGeokit\Filament\StateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelGeokit\Filament\StateResource;

class ListStates extends ListRecords
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
