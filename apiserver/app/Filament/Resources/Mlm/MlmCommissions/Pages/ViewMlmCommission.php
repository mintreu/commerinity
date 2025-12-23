<?php

namespace App\Filament\Resources\Mlm\MlmCommissions\Pages;

use App\Filament\Resources\Mlm\MlmCommissions\MlmCommissionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMlmCommission extends ViewRecord
{
    protected static string $resource = MlmCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
