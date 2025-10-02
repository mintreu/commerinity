<?php

namespace App\Filament\Resources\Promotion\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelMoney\Filament\Tables\Columns\MoneyColumn;

class SaleProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'sale_products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('discount_amount')
            ->columns([

                Tables\Columns\TextColumn::make('product.name'),
//                Tables\Columns\TextColumn::make('product.url'),
                MoneyColumn::make('product.price'),
                MoneyColumn::make('product.cheapestTier.price')->badge(),
                Tables\Columns\TextColumn::make('discount_amount')->label('Discount (% / Amount)'),
                MoneyColumn::make('sale_price'),
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
