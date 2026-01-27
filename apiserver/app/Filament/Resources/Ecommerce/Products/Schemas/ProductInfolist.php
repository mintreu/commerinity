<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('General')
                            ->columns(3)
                            ->schema([

                                Section::make('Product Info')
                                    ->columnSpanFull()
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        // Thumbnail
                                        SpatieMediaLibraryImageEntry::make('displayImage')
                                            ->hiddenLabel()
                                            ->imageSize('250px')
                                            ->collection('displayImage'),

                                        // Info Box
                                        Grid::make(1)
                                            ->schema([
                                                // Product Name
                                                TextEntry::make('name')
                                                    ->size(TextSize::Large)
                                                    ->weight(FontWeight::ExtraBold)
                                                    ->color('primary')
                                                    ->hintAction(
                                                        Action::make('visit')
                                                            ->url(url: fn(Model $record) => config('app.client_url').'/product/'.$record->url,shouldOpenInNewTab:true)
                                                            ->icon('heroicon-m-globe-alt')->iconButton()
                                                    ),

                                                // Product SKU
                                                TextEntry::make('sku')
                                                    ->label('SKU')->inlineLabel(),

                                                // Product Price From Stock

                                                TextEntry::make('price')
                                                    ->money()
                                                    ->placeholder('-'),

                                                // Total Available Stock
                                                TextEntry::make('Current Stock')->inlineLabel()
                                                    ->getStateUsing(fn(Model $record) => $record->availableStocks()->sum('in_stock_quantity'))
                                                    ->size(TextSize::Medium),

                                                TextEntry::make('type')->badge()->inlineLabel(),
                                                TextEntry::make('status')->badge()->inlineLabel(),
                                            ]),

                                    ]),


                                TextEntry::make('short_description')
                                    ->label('Short Intro')
                                    ->placeholder('-')
                                    ->columnSpanFull()->html()->alignJustify(),

                                Section::make('Full Description')
                                    ->columnSpanFull()->collapsible()
                                    ->schema([
                                        TextEntry::make('description')
                                            ->hiddenLabel()
                                            ->placeholder('-')
                                            ->columnSpanFull()->html()->alignJustify(),
                                    ]),
                            ]),
                        Tab::make('Source')
                            ->schema([

                                TextEntry::make('parent.name')
                                    ->label('Parent')
                                    ->columnSpanFull()
                                    ->placeholder('-'),
                            ]),
                        Tab::make('Gallery')
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('bannerImage')
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->imageSize('250px')
                                    ->collection('bannerImage')
                            ]),

                        Tab::make('Store Config')
                            ->columns()
                            ->schema([


                                TextEntry::make('status')
                                    ->badge(),
                                IconEntry::make('is_returnable')
                                    ->boolean(),
                                TextEntry::make('return_days')
                                    ->numeric(),
                                TextEntry::make('view_count')
                                    ->numeric(),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                        Tab::make('Config')
                            ->schema([

                                TextEntry::make('filterGroup.name')
                                    ->label('Filter group'),
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->placeholder('-'),
                            ]),
                    ]),
            ]);
    }
}
