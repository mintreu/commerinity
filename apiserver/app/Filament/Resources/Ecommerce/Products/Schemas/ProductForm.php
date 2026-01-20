<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Casts\ProductStatusCast;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;



class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->lazy()
                                    ->afterStateUpdated(fn($state,Set $set) => $set('url',Str::slug($state))),
                                Select::make('parent_id')
                                    ->relationship('parent', 'name'),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->required(),
                                TextInput::make('url')
                                    ->unique('products','url')
                                    ->required(),
                                TextInput::make('type')
                                    ->required(),

                                Select::make('status')
                                    ->options(ProductStatusCast::class)
                                    ->default('Draft')
                                    ->required(),
                                Toggle::make('is_returnable')
                                    ->live()
                                    ->required(),
                                TextInput::make('return_days')
                                    ->required()
                                    ->visible(fn(Get $get) => $get('is_returnable'))
                                    ->numeric()
                                    ->default(7),


                            ]),
                        Tab::make('About')
                            ->schema([

                                Textarea::make('short_description')
                                    ->columnSpanFull(),

                                RichEditor::make('description')
                                    ->columnSpanFull(),



                            ]),
                        Tab::make('Media')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('display')
                                    ->label('Thumbnail')
                                    ->image()
                                    ->multiple(false)
                                    ->imageEditor()
                                    ->collection('displayImage'),

                                SpatieMediaLibraryFileUpload::make('banner')
                                    ->label('Gallery')
                                    ->image()
                                    ->multiple()
                                    ->imageEditor()
                                    ->collection('bannerImage'),
                            ]),
                        Tab::make('Config')
                            ->schema([
                                Select::make('filter_group_id')
                                    ->relationship('filterGroup', 'name')
                                    ->required(),
                                Select::make('category_id')
                                    ->relationship('category', 'name'),
                            ]),
                        Tab::make('Additional')
                            ->schema([

                                TextInput::make('seo_meta'),

                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$'),

                                TextInput::make('view_count')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),







            ]);
    }



    public static function statusOptions(): array
    {
        return collect(ProductStatusCast::cases())
            ->mapWithKeys(fn (ProductStatusCast $status) => [$status->value => $status->getLabel()])
            ->toArray();
    }


}
