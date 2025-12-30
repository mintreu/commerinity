<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Casts\ProductStatusCast;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->default('Unnamed Product'),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Select::make('filter_group_id')
                    ->relationship('filterGroup', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('seo_meta'),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                TextInput::make('product_display_id')
                    ->numeric(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                Select::make('status')
                    ->options(ProductStatusCast::class)
                    ->default('Draft')
                    ->required(),
                Toggle::make('is_returnable')
                    ->required(),
                TextInput::make('return_days')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
