<?php

namespace App\Filament\Resources\Ecommerce\Sales\RelationManagers;

use App\Services\MoneyService;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SaleProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'saleProducts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('product.price')
                    ->label('Base Price')
                    ->formatStateUsing(fn ($state) => MoneyService::format((int) $state)),
                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->numeric(),
                TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->formatStateUsing(fn ($state) => MoneyService::format((int) $state)),
                TextColumn::make('starts_from')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_till')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
