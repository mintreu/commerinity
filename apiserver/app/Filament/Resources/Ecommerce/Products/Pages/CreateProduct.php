<?php

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Filament\Resources\Ecommerce\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
