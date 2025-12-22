<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource;

class ViewFilter extends ViewRecord
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
