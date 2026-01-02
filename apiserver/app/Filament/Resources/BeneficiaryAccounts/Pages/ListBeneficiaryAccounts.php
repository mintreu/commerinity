<?php

namespace App\Filament\Resources\BeneficiaryAccounts\Pages;

use App\Filament\Resources\BeneficiaryAccounts\BeneficiaryAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiaryAccounts extends ListRecords
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
