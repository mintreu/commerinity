<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Pages;

use App\Filament\Resources\Ecommerce\ProductStocks\ProductStockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductStock extends ViewRecord
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
