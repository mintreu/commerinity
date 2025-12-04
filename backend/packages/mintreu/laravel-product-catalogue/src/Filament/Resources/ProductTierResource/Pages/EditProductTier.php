<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource;

class EditProductTier extends EditRecord
{
    protected static string $resource = ProductTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
