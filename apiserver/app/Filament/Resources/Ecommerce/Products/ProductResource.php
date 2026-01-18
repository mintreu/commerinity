<?php

namespace App\Filament\Resources\Ecommerce\Products;

use App\Filament\Resources\Ecommerce\Products\Pages\CreateProduct;
use App\Filament\Resources\Ecommerce\Products\Pages\EditProduct;
use App\Filament\Resources\Ecommerce\Products\Pages\ListProducts;
use App\Filament\Resources\Ecommerce\Products\Pages\ManageStock;
use App\Filament\Resources\Ecommerce\Products\Pages\ViewProduct;
use App\Filament\Resources\Ecommerce\Products\Schemas\ProductForm;
use App\Filament\Resources\Ecommerce\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Ecommerce\Products\Tables\ProductsTable;
use App\Models\Ecommerce\Product;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $recordRouteKeyName = 'url';



    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            // ...
            Pages\ViewProduct::class,
            Pages\ManageStock::class,
            Pages\EditProduct::class,
        ]);
    }


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
            'view' => ViewProduct::route('/{record:url}'),
            'edit' => EditProduct::route('/{record:url}/edit'),
            'stock' => ManageStock::route('/{record:url}/stocks')
        ];
    }
}
