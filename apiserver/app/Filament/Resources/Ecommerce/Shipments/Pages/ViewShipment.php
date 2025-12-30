<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Pages;

use App\Filament\Resources\Ecommerce\Shipments\ShipmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
