<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Services\MoneyService;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Model;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $currency = MoneyService::make()->getCurrencyCode();

        return $schema->components([
            Tabs::make('Product')
                ->columnSpanFull()
                ->contained(false)
                ->tabs([
                    Tab::make('Overview')
                        ->schema([
                            // HERO (Image + Primary Info)
                            Flex::make([
                                Section::make()
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('displayImage')
                                            ->hiddenLabel()
                                            ->collection('displayImage')
                                            ->imageSize('320px')
                                            ->extraImgAttributes([
                                                'class' => 'rounded-2xl ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm object-cover',
                                            ])
                                            ->placeholder('-'),
                                    ])
                                    ->grow(false),

                                Group::make([
                                    Section::make('Product')
                                        ->schema([
                                            TextEntry::make('name')
                                                ->hiddenLabel()
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::ExtraBold)
                                                ->hintAction(
                                                    Action::make('visit')
                                                        ->label('Open on store')
                                                        ->icon('heroicon-m-arrow-top-right-on-square')
                                                        ->url(
                                                            fn (Model $record) => rtrim(config('app.client_url'), '/') . '/product/' . $record->url,
                                                            true
                                                        )
                                                ),

                                            Grid::make([
                                                'default' => 2,
                                                'md' => 4,
                                            ])->schema([
                                                TextEntry::make('sku')
                                                    ->label('SKU')
                                                    ->placeholder('-')
                                                    ->badge(),

                                                TextEntry::make('price')
                                                    ->label('Price')
                                                    ->money($currency)
                                                    ->placeholder('-')
                                                    ->badge(),

                                                TextEntry::make('type')
                                                    ->label('Type')
                                                    ->badge()
                                                    ->placeholder('-'),

                                                TextEntry::make('status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->placeholder('-'),
                                            ]),

                                            Grid::make([
                                                'default' => 2,
                                                'md' => 4,
                                            ])->schema([
                                                TextEntry::make('current_stock')
                                                    ->label('Current Stock')
                                                    ->getStateUsing(fn (Model $record) => (int) $record->availableStocks()->sum('in_stock_quantity'))
                                                    ->placeholder('0')
                                                    ->badge(),

                                                TextEntry::make('view_count')
                                                    ->label('Views')
                                                    ->numeric()
                                                    ->placeholder('0')
                                                    ->badge(),

                                                IconEntry::make('is_returnable')
                                                    ->label('Returnable')
                                                    ->boolean(),

                                                TextEntry::make('return_days')
                                                    ->label('Return Days')
                                                    ->numeric()
                                                    ->placeholder('-')
                                                    ->badge(),
                                            ]),
                                        ]),
                                ]),
                            ])->from('md')->columnSpanFull(),

                            Section::make('Short Intro')
                                ->collapsible()
                                ->schema([
                                    TextEntry::make('short_description')
                                        ->hiddenLabel()
                                        ->placeholder('-')
                                        ->html()
                                        ->alignJustify()
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),

                            Section::make('Full Description')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    TextEntry::make('description')
                                        ->hiddenLabel()
                                        ->placeholder('-')
                                        ->html()
                                        ->alignJustify()
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columns(1),

                    Tab::make('Catalog')
                        ->schema([
                            Section::make('Source')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('parent.name')
                                        ->label('Parent Product')
                                        ->placeholder('-'),

                                    TextEntry::make('category.name')
                                        ->label('Category')
                                        ->placeholder('-'),

                                    TextEntry::make('filterGroup.name')
                                        ->label('Filter Group')
                                        ->placeholder('-'),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Gallery')
                        ->schema([
                            Section::make('Images')
                                ->schema([
                                    // If bannerImage collection has multiple images, this will render them.
                                    SpatieMediaLibraryImageEntry::make('bannerImage')
                                        ->hiddenLabel()
                                        ->collection('bannerImage')
                                        ->imageSize('260px')
                                        ->columnSpanFull()
                                        ->extraImgAttributes([
                                            'class' => 'rounded-xl ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm object-cover',
                                        ])
                                        ->placeholder('-'),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Store & System')
                        ->schema([
                            Section::make('Store Config')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Updated')
                                        ->dateTime()
                                        ->placeholder('-'),
                                ])
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
