<?php

namespace App\Filament\Resources\Promotion\SaleResource\Pages;

use App\Filament\Resources\Promotion\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelMoney\LaravelMoney;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public  function table(Table $table): Table
    {
        return $table
            ->defaultGroup('action_type')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('description')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->money(LaravelMoney::defaultCurrency())
                    ->sortable(),
                Tables\Columns\TextColumn::make('action_type')
                    ->badge()
                    ->searchable(),

                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
//                Tables\Columns\IconColumn::make('condition_type')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('end_other_rules')
//                    ->boolean(),


                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
