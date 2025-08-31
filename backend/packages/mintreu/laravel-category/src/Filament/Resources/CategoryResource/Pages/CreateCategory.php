<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use App\Filament\Common\Schema\AdjacencySchema\HasAdjacencyFormSchema;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelCategory\Filament\Resources\CategoryResource;

class CreateCategory extends CreateRecord
{
    use HasAdjacencyFormSchema;

    protected static string $resource = CategoryResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)->schema($this->getAdjacencyFormSchema());
    }
}
