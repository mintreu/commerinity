<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Actions;
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }


    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->components($this->getAdjacencyFormSchema());
    }


}
