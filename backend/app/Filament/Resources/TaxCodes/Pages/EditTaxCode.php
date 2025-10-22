<?php

namespace App\Filament\Resources\TaxCodeResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\TaxCodes\TaxCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxCode extends EditRecord
{
    protected static string $resource = TaxCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
