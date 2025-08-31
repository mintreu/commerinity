<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use App\Filament\Common\Schema\AdjacencySchema\HasAdjacencyFormSchema;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelCategory\Filament\Resources\CategoryResource;

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
