<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('Tabs')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Overview')
                            ->columns(3)
                            ->schema([

                                SpatieMediaLibraryImageEntry::make('thumbnail')
                                    ->collection('displayImage'),


                                Fieldset::make('Product Info')
                                    ->columnSpan(2)
                                    ->schema([
                                        TextEntry::make('name'),
                                        TextEntry::make('url'),
                                        TextEntry::make('sku')
                                            ->label('SKU'),

                                    ]),

                                TextEntry::make('parent.name')
                                    ->label('Parent')
                                    ->placeholder('-'),

                                Section::make('Basic Config')
                                    ->aside()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('type'),
                                        TextEntry::make('status')
                                            ->badge(),
                                        IconEntry::make('is_returnable')
                                            ->boolean(),
                                        TextEntry::make('return_days')
                                            ->numeric(),
                                    ]),


                                TextEntry::make('short_description')
                                    ->label('Short Description')
                                    ->html()
                                    ->alignJustify()
                                    ->placeholder('-')
                                    ->columnSpanFull(),



                            ]),
                        Tab::make('Config')
                            ->schema([

                                TextEntry::make('filterGroup.name')
                                    ->label('Filter group'),
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->placeholder('-'),

                            ]),
                        Tab::make('About')
                            ->schema([
                                TextEntry::make('description')
                                    ->placeholder('-')
                                    ->label('Description')
                                    ->html()
                                    ->alignJustify()
                                    ->columnSpanFull(),

                            ]),
                        Tab::make('Media')
                            ->schema([

                                SpatieMediaLibraryImageEntry::make('thumbnail')
                                    ->collection('bannerImage'),

                            ]),
                        Tab::make('Additional')
                            ->schema([

                                TextEntry::make('price')
                                    ->money()
                                    ->placeholder('-'),

                                TextEntry::make('view_count')
                                    ->numeric(),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),


                            ]),
                    ]),










            ]);
    }
}
