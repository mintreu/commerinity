<?php

namespace App\Filament\Resources\BeneficiaryAccounts\Pages;

use App\Filament\Resources\BeneficiaryAccounts\BeneficiaryAccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBeneficiaryAccount extends ViewRecord
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
