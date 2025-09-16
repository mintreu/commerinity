<?php

namespace App\Filament\Resources\BeneficiaryAccountResource\Pages;

use App\Filament\Resources\BeneficiaryAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiaryAccounts extends ListRecords
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
