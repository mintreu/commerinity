<?php

namespace App\Filament\Resources\Ecommerce\Categories;

use App\Filament\Resources\Ecommerce\Categories\Pages\CreateCategory;
use App\Filament\Resources\Ecommerce\Categories\Pages\EditCategory;
use App\Filament\Resources\Ecommerce\Categories\Pages\ListCategories;
use App\Filament\Resources\Ecommerce\Categories\Pages\ViewCategory;
use App\Filament\Resources\Ecommerce\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Ecommerce\Categories\Schemas\CategoryInfolist;
use App\Filament\Resources\Ecommerce\Categories\Tables\CategoriesTable;
use App\Models\Ecommerce\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
