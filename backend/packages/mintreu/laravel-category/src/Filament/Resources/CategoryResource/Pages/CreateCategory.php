<?php

namespace Mintreu\LaravelCategory\Filament\Resources\CategoryResource\Pages;

use Filament\Schemas\Schema;
use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelCategory\Filament\Resources\CategoryResource;
use Mintreu\LaravelCategory\Support\AdjacencySchema\HasAdjacencyFormSchema;

class CreateCategory extends CreateRecord
{
    use HasAdjacencyFormSchema;

    protected static string $resource = CategoryResource::class;

    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->components($this->getAdjacencyFormSchema());
    }
}
