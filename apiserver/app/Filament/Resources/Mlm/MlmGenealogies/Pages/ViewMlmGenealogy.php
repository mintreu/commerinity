<?php

namespace App\Filament\Resources\Mlm\MlmGenealogies\Pages;

use App\Filament\Resources\Mlm\MlmGenealogies\MlmGenealogyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMlmGenealogy extends ViewRecord
{
    protected static string $resource = MlmGenealogyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
