<?php

namespace App\Filament\Resources\Ecommerce\Products;

use App\Filament\Resources\Ecommerce\Products\Pages\CreateProduct;
use App\Filament\Resources\Ecommerce\Products\Pages\EditProduct;
use App\Filament\Resources\Ecommerce\Products\Pages\ListProducts;
use App\Filament\Resources\Ecommerce\Products\Pages\ViewProduct;
use App\Filament\Resources\Ecommerce\Products\Schemas\ProductForm;
use App\Filament\Resources\Ecommerce\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Ecommerce\Products\Tables\ProductsTable;
use App\Models\Ecommerce\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
