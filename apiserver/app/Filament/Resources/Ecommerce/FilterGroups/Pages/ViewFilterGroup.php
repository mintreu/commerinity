<?php

namespace App\Filament\Resources\Ecommerce\FilterGroups\Pages;

use App\Filament\Resources\Ecommerce\FilterGroups\FilterGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFilterGroup extends ViewRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
