<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks;

use App\Filament\Resources\Ecommerce\ProductStocks\Pages\CreateProductStock;
use App\Filament\Resources\Ecommerce\ProductStocks\Pages\EditProductStock;
use App\Filament\Resources\Ecommerce\ProductStocks\Pages\ListProductStocks;
use App\Filament\Resources\Ecommerce\ProductStocks\Pages\ViewProductStock;
use App\Filament\Resources\Ecommerce\ProductStocks\Schemas\ProductStockForm;
use App\Filament\Resources\Ecommerce\ProductStocks\Schemas\ProductStockInfolist;
use App\Filament\Resources\Ecommerce\ProductStocks\Tables\ProductStocksTable;
use App\Models\Ecommerce\ProductStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductStockResource extends Resource
{
    protected static ?string $model = ProductStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $pluralModelLabel = 'Purchase Entry';
    protected static ?string $pluralLabel = 'Purchase Entry';
    protected static string|null|\UnitEnum $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ProductStockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductStockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductStocksTable::configure($table);
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
            'index' => ListProductStocks::route('/'),
            'create' => CreateProductStock::route('/create'),
            'view' => ViewProductStock::route('/{record}'),
            'edit' => EditProductStock::route('/{record}/edit'),
        ];
    }
}
