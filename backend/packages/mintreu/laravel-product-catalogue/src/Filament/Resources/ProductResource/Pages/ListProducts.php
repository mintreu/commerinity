<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Mintreu\LaravelMoney\Filament\Tables\Columns\MoneyColumn;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource;
use Filament\Tables;
use Filament\Tables\Table;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return array_merge(
            array_combine(
                array_map(fn($case) => $case->value, ProductTypeCast::cases()),
                array_map(fn($case) => Tab::make()
                    ->icon($case->getIcon())
                    ->modifyQueryUsing(fn( $query) => $query->where('type', $case->value)),
                    ProductTypeCast::cases())
            ),
            [
                'all' => Tab::make()->icon('heroicon-s-table-cells'),
            ]
        );

    }


    public  function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([

                    Split::make([
                        SpatieMediaLibraryImageColumn::make('thumbnail')
                            ->label('Display')
                            ->extraImgAttributes(['class' => 'mx-auto object-cover'])
                            ->collection('displayImage')
                            ->width('200px')
                            ->height('300px'),


                        Stack::make([
                            TextColumn::make('name')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::SemiBold)
                                ->searchable(),


                            TextColumn::make('sku')
                                ->label('SKU')
                                ->prefix('Sku: ')
                                ->searchable(),

                            Split::make([
                                TextColumn::make('type')
                                    ->badge()
                                    ->searchable(),

                                TextColumn::make('status')
                                    ->badge()
                                    ->searchable(),
                            ]),

                            TextColumn::make('filterGroup.name')
                                ->badge()
                                ->sortable(),

                            Split::make([

                                MoneyColumn::make('price'),

                                TextColumn::make('reward_point')
                                    ->numeric()
                                    ->badge()
                                    ->suffix(' points')
                                    ->sortable(),
                            ]),

                            TextColumn::make('view_count')
                                ->numeric()
                                ->suffix(' views')
                                ->badge()
                                ->sortable(),

                        ])->extraAttributes(['class' => 'grow'])
                    ]),


//                    Tables\Columns\TextColumn::make('parent_id')
//                        ->numeric()
//                        ->sortable(),
//
//
//
//                    Tables\Columns\TextColumn::make('category_id')
//                        ->numeric()
//                        ->sortable(),
//                    Tables\Columns\TextColumn::make('tenant_type')
//                        ->searchable(),
//                    Tables\Columns\TextColumn::make('tenant_id')
//                        ->numeric()
//                        ->sortable(),
//
//
//                    Tables\Columns\IconColumn::make('is_returnable')
//                        ->boolean(),


//                    Tables\Columns\TextColumn::make('created_at')
//                        ->dateTime()
//                        ->sortable()
//                        ->toggleable(isToggledHiddenByDefault: true),
//                    Tables\Columns\TextColumn::make('updated_at')
//                        ->dateTime()
//                        ->sortable()
//                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->contentGrid([
                'default' => 1,
                'md'    => 2
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }





}
