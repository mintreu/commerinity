<?php

namespace App\Filament\Resources\Ecommerce\Filters\Pages;

use App\Filament\Resources\Ecommerce\Filters\FilterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFilters extends ListRecords
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
