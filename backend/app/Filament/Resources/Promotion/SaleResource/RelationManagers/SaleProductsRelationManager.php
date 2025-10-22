<?php

namespace App\Filament\Resources\Promotion\SaleResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelMoney\Filament\Tables\Columns\MoneyColumn;

class SaleProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'sale_products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('discount_amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('discount_amount')
            ->columns([

                TextColumn::make('product.name'),
//                Tables\Columns\TextColumn::make('product.url'),
                MoneyColumn::make('product.price'),
                MoneyColumn::make('product.cheapestTier.price')->badge(),
                TextColumn::make('discount_amount')->label('Discount (% / Amount)'),
                MoneyColumn::make('sale_price'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
