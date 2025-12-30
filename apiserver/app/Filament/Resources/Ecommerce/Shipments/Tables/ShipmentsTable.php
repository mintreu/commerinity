<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->searchable(),
                TextColumn::make('pickupAddress.title')
                    ->searchable(),
                TextColumn::make('deliveryAddress.title')
                    ->searchable(),
                TextColumn::make('total_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('shipping_method')
                    ->searchable(),
                TextColumn::make('provider')
                    ->searchable(),
                TextColumn::make('shipping_provider_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('provider_channel_id')
                    ->searchable(),
                TextColumn::make('provider_order_id')
                    ->searchable(),
                TextColumn::make('shipment_id')
                    ->searchable(),
                TextColumn::make('tracking_id')
                    ->searchable(),
                TextColumn::make('shipped_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('return_initiated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('cod')
                    ->boolean(),
                TextColumn::make('cod_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cod_status')
                    ->searchable(),
                TextColumn::make('cod_collected_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cod_remitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('charge')
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
                ]),
            ]);
    }
}
