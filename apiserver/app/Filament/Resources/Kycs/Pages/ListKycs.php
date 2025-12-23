<?php

namespace App\Filament\Resources\Kycs\Pages;

use App\Filament\Resources\Kycs\KycResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKycs extends ListRecords
{
    protected static string $resource = KycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
