<?php

namespace App\Filament\Resources\Ecommerce\Products\Tables;

use App\Filament\Exports\Ecommerce\ProductExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('displayImage')->label(null)->collection('displayImage'),
                TextColumn::make('name')
                    ->searchable(),
//                TextColumn::make('parent.name')
//                    ->searchable(),
//                TextColumn::make('sku')
//                    ->label('SKU')
//                    ->searchable(),
//                TextColumn::make('url')
//                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('filterGroup.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),

                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
//                IconColumn::make('is_returnable')
//                    ->boolean(),
//                TextColumn::make('return_days')
//                    ->numeric()
//                    ->sortable(),
                TextColumn::make('view_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ExportBulkAction::make()
                        ->exporter(ProductExporter::class),
                ]),
            ]);
    }
}
