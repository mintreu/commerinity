<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Schemas\ProductEditFormSchema;
use Mintreu\LaravelProductCatalogue\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ProductEditFormSchema::config());
    }


    public static function getRelations(): array
    {
        return [
            \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager::class,
            \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers\TiersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages\ListProducts::route('/'),
            'create' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages\CreateProduct::route('/create'),
            'view' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages\ViewProduct::route('/{record:url}'),
            'edit' => \Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages\EditProduct::route('/{record:url}/edit'),
        ];
    }
}
