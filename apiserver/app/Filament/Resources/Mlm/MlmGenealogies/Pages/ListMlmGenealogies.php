<?php

namespace App\Filament\Resources\Mlm\MlmGenealogies\Pages;

use App\Filament\Resources\Mlm\MlmGenealogies\MlmGenealogyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMlmGenealogies extends ListRecords
{
    protected static string $resource = MlmGenealogyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
