<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource;

class ListFilters extends ListRecords
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
