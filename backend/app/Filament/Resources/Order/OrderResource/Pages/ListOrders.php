<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Order\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
