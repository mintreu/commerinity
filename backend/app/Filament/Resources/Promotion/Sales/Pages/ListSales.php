<?php

namespace App\Filament\Resources\Promotion\Sales\Pages;

use Filament\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Promotion\Sales\SaleResource;
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
            CreateAction::make(),
        ];
    }


    public  function table(Table $table): Table
    {
        return $table
            ->defaultGroup('action_type')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('description')
//                    ->searchable(),
                TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->money(LaravelMoney::defaultCurrency(),100)
                    ->sortable(),
                TextColumn::make('action_type')
                    ->badge()
                    ->searchable(),

                IconColumn::make('status')
                    ->boolean(),
//                Tables\Columns\IconColumn::make('condition_type')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('end_other_rules')
//                    ->boolean(),


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
