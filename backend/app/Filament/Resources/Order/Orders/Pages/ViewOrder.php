<?php

namespace App\Filament\Resources\Order\Orders\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Order\Orders\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
