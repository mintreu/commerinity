<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelCategory\Filament\Resources\CategoryResource;
use Mintreu\LaravelCategory\Support\AdjacencySchema\HasAdjacencyFormSchema;

class EditCategory extends EditRecord
{

    use HasAdjacencyFormSchema;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }


    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->getAdjacencyFormSchema());
    }


}
