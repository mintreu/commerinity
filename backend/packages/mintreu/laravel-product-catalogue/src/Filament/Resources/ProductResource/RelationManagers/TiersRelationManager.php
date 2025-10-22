<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
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
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;

class TiersRelationManager extends RelationManager
{

    protected static string $relationship = 'tiers';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('init_quantity')
                    ->required()
                    ->label(__('Stock Quantity'))
                    ->minValue(fn() =>  max(0, $this->ownerRecord->sold_quantity))
                    ->numeric()
                    ->maxLength(255),

                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required(),

                TextInput::make('min_quantity')
                    ->label('Minimum Purchase Quantity')
                    ->helperText('Minimum number of units that must be ordered.')
                    ->numeric()
                    ->required(),

                TextInput::make('max_quantity')
                    ->label('Maximum Purchase Quantity')
                    ->helperText('Maximum number of units that must be ordered.')
                    ->numeric()
                    ->required(),



                TextInput::make('wholesale_unit_quantity')
                    ->label('Units per Wholesale Pack')
                    ->helperText('Number of individual units in one wholesale pack (e.g., 24).')
                    ->visible(fn() => $this->ownerRecord->type == ProductTypeCast::WHOLESALE)
                    ->numeric()
                    ->required(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('init_quantity')
            ->columns([
                TextColumn::make('init_quantity'),
                TextColumn::make('sold_quantity')->label('Sold Quantity'),
                TextColumn::make('in_stock_quantity')->label('In Stock Quantity'),
                IconColumn::make('in_stock')->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('updated_at')->dateTime()->toggleable(),
                TextColumn::make('created_at')->dateTime()->toggleable()->toggledHiddenByDefault(),
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
