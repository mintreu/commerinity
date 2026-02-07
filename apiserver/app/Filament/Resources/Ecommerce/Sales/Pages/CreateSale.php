<?php

namespace App\Filament\Resources\Ecommerce\Sales\Pages;

use App\Filament\Resources\Ecommerce\Sales\SaleResource;
use App\Services\Ecommerce\SaleManager;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function afterCreate(): void
    {
        SaleManager::make()->reindexSaleableProducts();
    }
}
