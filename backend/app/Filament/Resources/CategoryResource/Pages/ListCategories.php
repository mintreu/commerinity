<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Common\Schema\AdjacencySchema\HasAdjacencyTableSchema;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListCategories extends ListRecords
{
    use HasAdjacencyTableSchema;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    /**
     * Builds the form used by the Filament resource for creating topics.
     */
    public function form(Form $form): Form
    {
        return $form->schema(array_merge(
            self::$resource::getForm(),
            self::$resource::getParentForm(),
        ));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getAdjacencyTableColumns())
            ->filters($this->getAdjacencyTableFilters())
            ->actions($this->getAdjacencyTableActions())
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

}
