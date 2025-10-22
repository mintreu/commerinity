<?php

namespace App\Filament\Resources\TaxCodeResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TaxCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxCodes extends ListRecords
{
    protected static string $resource = TaxCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
