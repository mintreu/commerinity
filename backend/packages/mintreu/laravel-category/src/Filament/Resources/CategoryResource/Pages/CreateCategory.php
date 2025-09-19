<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelCategory\Filament\Resources\CategoryResource;
use Mintreu\LaravelCategory\Support\AdjacencySchema\HasAdjacencyFormSchema;

class CreateCategory extends CreateRecord
{
    use HasAdjacencyFormSchema;

    protected static string $resource = CategoryResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->getAdjacencyFormSchema());
    }
}
