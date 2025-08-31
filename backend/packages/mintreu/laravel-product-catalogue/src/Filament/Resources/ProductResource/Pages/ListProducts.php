<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
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
            Actions\CreateAction::make(),
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
                Tables\Columns\Layout\Stack::make([

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                            ->label('Display')
                            ->extraImgAttributes(['class' => 'mx-auto object-cover'])
                            ->collection('displayImage')
                            ->width('200px')
                            ->height('300px'),


                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('name')
                                ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                                ->weight(FontWeight::SemiBold)
                                ->searchable(),


                            Tables\Columns\TextColumn::make('sku')
                                ->label('SKU')
                                ->prefix('Sku: ')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('url')
                                ->prefix('slug: ')
                                ->searchable(),

                            Tables\Columns\Layout\Split::make([
                                Tables\Columns\TextColumn::make('type')
                                    ->badge()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('status')
                                    ->badge()
                                    ->searchable(),
                            ]),

                            Tables\Columns\TextColumn::make('filterGroup.name')
                                ->badge()
                                ->sortable(),

                            Tables\Columns\Layout\Split::make([

                                Tables\Columns\TextColumn::make('price')
                                    ->default(0)
                                    ->money(LaravelMoney::getCurrencySymbol())
                                    ->badge(),

                                Tables\Columns\TextColumn::make('reward_point')
                                    ->numeric()
                                    ->badge()
                                    ->suffix(' points')
                                    ->sortable(),
                            ]),

                            Tables\Columns\TextColumn::make('view_count')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }





}
