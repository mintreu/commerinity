<?php

namespace App\Filament\Resources\TaxCodeResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\TaxCodes\TaxCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxCode extends ViewRecord
{
    protected static string $resource = TaxCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
