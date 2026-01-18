<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
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
                    ->contained(false)
                    ->tabs([
                        Tab::make('Overview')
                            ->columns(3)
                            ->schema([

                                SpatieMediaLibraryFileUpload::make('display')
                                    ->label('Thumbnail')
                                    ->image()
                                    ->multiple(false)
                                    ->imageEditor()
                                    ->collection('displayImage'),

                                Fieldset::make('Product Info')
                                    ->columns(1)
                                    ->columnSpan(2)
                                    ->schema([

                                        TextInput::make('name')
                                            ->required()
                                            ->lazy()
                                            ->afterStateUpdated(fn($state,Set $set) => $set('url',Str::slug($state))),

                                        TextInput::make('url')
                                            ->unique('products','url')
                                            ->required(),

                                        TextInput::make('sku')
                                            ->label('SKU')
                                            ->required(),
                                    ]),


                                Section::make('General Config')
                                    ->columnSpanFull()
                                    ->aside()
                                    ->schema([

                                        Select::make('parent_id')
                                            ->visible(fn(Model $record) => $record->type != ProductTypeCast::SIMPLE->value)
                                            ->relationship('parent', 'name'),


                                        TextInput::make('type')
                                            ->required(),

                                        Select::make('status')
                                            ->options(ProductStatusCast::class)
                                            ->default('Draft')
                                            ->required(),
                                    ]),


                                Textarea::make('short_description')
                                    ->label('Short Overview')
                                    ->columnSpanFull(),



                            ]),

                        Tab::make('Config')
                            ->schema([

                                Section::make('Returnable')
                                    ->schema([

                                        Toggle::make('is_returnable')
                                            ->live()
                                            ->required(),
                                        TextInput::make('return_days')
                                            ->required()
                                            ->visible(fn(Get $get) => $get('is_returnable'))
                                            ->numeric()
                                            ->default(7),
                                    ]),


                                Select::make('filter_group_id')
                                    ->relationship('filterGroup', 'name')
                                    ->required(),
                                Select::make('category_id')
                                    ->relationship('category', 'name'),
                            ]),
                        Tab::make('About')
                            ->schema([
                                RichEditor::make('description')
                                    ->columnSpanFull(),

                            ]),
                        Tab::make('Media')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('banner')
                                    ->label('Gallery')
                                    ->image()
                                    ->multiple()
                                    ->imageEditor()
                                    ->collection('bannerImage'),
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
}
