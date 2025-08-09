<?php

namespace Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource;

class ListFilterGroups extends ListRecords
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
