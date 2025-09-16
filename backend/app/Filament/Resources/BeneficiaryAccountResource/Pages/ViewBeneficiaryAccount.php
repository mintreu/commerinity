<?php

namespace App\Filament\Resources\BeneficiaryAccountResource\Pages;

use App\Filament\Resources\BeneficiaryAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBeneficiaryAccount extends ViewRecord
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
