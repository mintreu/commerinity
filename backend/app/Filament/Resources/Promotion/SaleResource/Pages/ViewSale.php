<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
