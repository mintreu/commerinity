<?php

namespace App\Filament\Resources\Ecommerce\FilterGroups\Pages;

use App\Filament\Resources\Ecommerce\FilterGroups\FilterGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFilterGroup extends CreateRecord
{
    protected static string $resource = FilterGroupResource::class;
}
