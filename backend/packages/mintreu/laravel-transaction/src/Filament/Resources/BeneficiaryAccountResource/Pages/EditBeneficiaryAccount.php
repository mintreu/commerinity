<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource;

class EditBeneficiaryAccount extends EditRecord
{
    protected static string $resource = BeneficiaryAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
