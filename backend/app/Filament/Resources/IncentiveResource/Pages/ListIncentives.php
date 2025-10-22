<?php

namespace App\Filament\Resources\IncentiveResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\IncentiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncentives extends ListRecords
{
    protected static string $resource = IncentiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
