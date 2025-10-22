<?php

namespace App\Filament\Resources\Carts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Carts\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
