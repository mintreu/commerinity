<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource;

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
