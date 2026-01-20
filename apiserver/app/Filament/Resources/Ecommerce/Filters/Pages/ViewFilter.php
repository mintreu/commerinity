<?php

namespace App\Filament\Resources\Ecommerce\Filters\Pages;

use App\Filament\Resources\Ecommerce\Filters\FilterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFilter extends ViewRecord
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
