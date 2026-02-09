<?php

namespace App\Filament\Resources\Ecommerce\Products\Tables;

use App\Casts\ProductTypeCast;
use App\Filament\Exports\Ecommerce\ProductExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('displayImage')
                    ->label(null)->collection('displayImage')
                    ->size('100px')
                    ->default('https://placehold.co/600x400'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('filterGroup.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),

                TextColumn::make('orderItems_count')
                    ->counts('orderItems')
                    ->default(0)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
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

                SelectFilter::make('type')
                    ->options(fn() => collect(ProductTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))

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
