<?php

namespace App\Filament\Resources\FilterGroupResource\Pages;

use App\Filament\Resources\FilterGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFilterGroup extends ViewRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
