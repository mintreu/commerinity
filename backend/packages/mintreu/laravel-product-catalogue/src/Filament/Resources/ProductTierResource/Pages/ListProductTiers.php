<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource;

class ListProductTiers extends ListRecords
{
    protected static string $resource = ProductTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
