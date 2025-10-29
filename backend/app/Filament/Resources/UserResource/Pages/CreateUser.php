<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use UserResource\Schema\HasUserFormSchema;

    protected static string $resource = UserResource::class;


    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->getUserCreationFormSchema());
    }




}
