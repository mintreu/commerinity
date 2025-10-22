<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;

use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Schemas\HasBeneficiaryCreationFormSchema;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource;

class CreateBeneficiaryAccount extends CreateRecord
{
    use HasBeneficiaryCreationFormSchema;
    protected static string $resource = BeneficiaryAccountResource::class;



    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components($this->getBeneficiaryCreationFormSchema());
    }


}
