<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use UserResource\Schema\HasUserFormSchema;
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),

        ];
    }


    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->getUserUpdateFormSchema());
    }


}
