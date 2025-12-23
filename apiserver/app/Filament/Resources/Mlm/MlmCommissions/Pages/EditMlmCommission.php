<?php

namespace App\Filament\Resources\Mlm\MlmCommissions\Pages;

use App\Filament\Resources\Mlm\MlmCommissions\MlmCommissionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMlmCommission extends EditRecord
{
    protected static string $resource = MlmCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
