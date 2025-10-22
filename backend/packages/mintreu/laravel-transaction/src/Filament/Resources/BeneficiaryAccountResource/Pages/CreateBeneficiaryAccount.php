<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource\Pages;

use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource;

class CreateBeneficiaryAccount extends CreateRecord
{
    use BeneficiaryAccountResource\Schemas\HasBeneficiaryCreationFormSchema;
    protected static string $resource = BeneficiaryAccountResource::class;



    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema($this->getBeneficiaryCreationFormSchema());
    }


}
