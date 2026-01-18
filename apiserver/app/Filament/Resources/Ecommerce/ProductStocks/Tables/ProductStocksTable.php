<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('init_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sold_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('in_stock_quantity')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('in_stock')
                    ->boolean(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address.title')
                    ->searchable(),
                TextColumn::make('landing_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('profit_margin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('min_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wholesale_unit_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reward_points')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_commissionable')
                    ->boolean(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('purchase_invoice_number')
                    ->searchable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->searchable(),
                TextColumn::make('low_stock_threshold')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('notify_on_low_stock')
                    ->boolean(),
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
                ]),
            ]);
    }
}
