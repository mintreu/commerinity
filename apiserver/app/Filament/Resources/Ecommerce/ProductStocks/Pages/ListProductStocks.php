<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Pages;

use App\Filament\Resources\Ecommerce\ProductStocks\ProductStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductStocks extends ListRecords
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
