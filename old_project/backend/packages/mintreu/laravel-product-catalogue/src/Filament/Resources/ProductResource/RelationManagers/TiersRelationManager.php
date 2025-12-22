<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Schemas\ProductTierFormSchema;

class TiersRelationManager extends RelationManager
{

    protected static string $relationship = 'tiers';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(ProductTierFormSchema::configure());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('init_quantity')
            ->columns([
                Tables\Columns\TextColumn::make('init_quantity'),
                Tables\Columns\TextColumn::make('sold_quantity')->label('Sold Quantity'),
                Tables\Columns\TextColumn::make('in_stock_quantity')->label('In Stock Quantity'),
                Tables\Columns\IconColumn::make('in_stock')->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
