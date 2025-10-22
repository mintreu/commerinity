<?php

namespace App\Filament\Resources\DistributorResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistributor extends ViewRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
