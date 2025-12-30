<?php

namespace App\Filament\Resources\Ecommerce\Categories\Pages;

use App\Filament\Resources\Ecommerce\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
