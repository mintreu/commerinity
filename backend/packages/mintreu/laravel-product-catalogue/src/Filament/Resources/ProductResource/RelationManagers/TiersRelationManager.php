<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;

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
            ->schema([

                Forms\Components\Grid::make(2)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Section::make('Purchase Info')
                            ->columnSpan(1)
                            ->schema([

                                Forms\Components\Select::make('product_supplier_id')
                                    ->label('Supplier')
                                    ->relationship('productSupplier','name'),

                                Forms\Components\TextInput::make('purchase_invoice_id')
                                    ->label('Purchase Invoice'),

                                MoneyInput::make('landing_cost')


                            ]),


                        Forms\Components\Section::make('Stock Info')
                            ->columnSpan(1)
                            ->schema([

                                Forms\Components\TextInput::make('init_quantity')
                                    ->required()
                                    ->label(__('Stock Quantity'))
                                    ->minValue(fn() =>  max(0, $this->ownerRecord->sold_quantity))
                                    ->numeric()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('min_quantity')
                                    ->label('Minimum Purchase Quantity')
                                    ->helperText('Minimum number of units that must be ordered.')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('max_quantity')
                                    ->label('Maximum Purchase Quantity')
                                    ->helperText('Maximum number of units that must be ordered.')
                                    ->numeric()
                                    ->required(),



                                Forms\Components\TextInput::make('wholesale_unit_quantity')
                                    ->label('Units per Wholesale Pack')
                                    ->helperText('Number of individual units in one wholesale pack (e.g., 24).')
                                    ->visible(fn() => $this->ownerRecord->type == ProductTypeCast::WHOLESALE)
                                    ->numeric()
                                    ->required(),

                            ]),
                    ]),



                Forms\Components\Section::make('Tire Info')
                    ->columnSpanFull()
                    ->columns()
                    ->schema([

                        Forms\Components\TextInput::make('profit_margin')
                            ->columnSpan(1)
                            ->suffix('%'),

                        MoneyInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->columnSpan(1)
                            ->required(),

                        Forms\Components\Select::make('address_id')
                            ->relationship('address','title')
                            ->columnSpanFull()

                    ]),









            ]);
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
