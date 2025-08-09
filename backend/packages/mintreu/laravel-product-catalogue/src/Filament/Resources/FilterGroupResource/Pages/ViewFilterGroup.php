<?php

namespace Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource;

class ViewFilterGroup extends ViewRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
