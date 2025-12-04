<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource;

class ViewProductTier extends ViewRecord
{
    protected static string $resource = ProductTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
