<?php

namespace App\Filament\Resources\Mlm\MlmCommissions\Pages;

use App\Filament\Resources\Mlm\MlmCommissions\MlmCommissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMlmCommissions extends ListRecords
{
    protected static string $resource = MlmCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
