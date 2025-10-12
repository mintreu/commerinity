<?php

namespace App\Filament\Resources\TaxCodeResource\Pages;

use App\Filament\Resources\TaxCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxCode extends ViewRecord
{
    protected static string $resource = TaxCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
