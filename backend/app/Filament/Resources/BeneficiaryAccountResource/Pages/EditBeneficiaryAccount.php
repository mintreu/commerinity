<?php

namespace App\Filament\Resources\BeneficiaryAccountResource\Pages;

use App\Filament\Resources\BeneficiaryAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBeneficiaryAccount extends EditRecord
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
