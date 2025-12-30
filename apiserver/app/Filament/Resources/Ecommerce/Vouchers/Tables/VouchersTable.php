<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('usage_per_customer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('coupon_usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('times_used')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('condition_type')
                    ->badge()
                    ->searchable(),
                IconColumn::make('end_other_rules')
                    ->boolean(),
                TextColumn::make('action_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_step')
                    ->searchable(),
                IconColumn::make('apply_to_shipping')
                    ->boolean(),
                IconColumn::make('free_shipping')
                    ->boolean(),
                TextColumn::make('min_cart_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_order')
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
