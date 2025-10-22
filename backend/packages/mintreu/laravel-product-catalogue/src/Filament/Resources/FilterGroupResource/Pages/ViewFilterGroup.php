<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource;

class ViewFilterGroup extends ViewRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
