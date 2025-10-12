<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';

//    public static function form(Form $form): Form
//    {
//        return $form
//            ->schema([
//                Forms\Components\TextInput::make('name')
//                    ->required()
//                    ->maxLength(255)
//                    ->default('Unnamed Product'),
//                Forms\Components\TextInput::make('parent_id')
//                    ->numeric(),
//                Forms\Components\TextInput::make('sku')
//                    ->label('SKU')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('url')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('type')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('filter_group_id')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('category_id')
//                    ->numeric(),
//                Forms\Components\TextInput::make('tenant_type')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('tenant_id')
//                    ->numeric(),
//                Forms\Components\Textarea::make('description')
//                    ->columnSpanFull(),
//                Forms\Components\Textarea::make('short_description')
//                    ->columnSpanFull(),
//                Forms\Components\TextInput::make('price')
//                    ->numeric()
//                    ->prefix('$'),
//                Forms\Components\TextInput::make('reward_point')
//                    ->required()
//                    ->numeric()
//                    ->default(0),
//                Forms\Components\Toggle::make('is_returnable')
//                    ->required(),
//                Forms\Components\TextInput::make('status')
//                    ->required()
//                    ->maxLength(255)
//                    ->default('Draft'),
//                Forms\Components\Textarea::make('status_feedback')
//                    ->columnSpanFull(),
//                Forms\Components\TextInput::make('view_count')
//                    ->required()
//                    ->numeric()
//                    ->default(0),
//                Forms\Components\TextInput::make('meta_data'),
//            ]);
//    }
//

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
