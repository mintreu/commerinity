<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductTierResource\Schemas;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Filament\Forms;
use Mintreu\LaravelProductCatalogue\Models\Product;


class ProductTierFormSchema
{


    public static function configure(): array
    {
        $instance = new static();
        return $instance->getFormSchema();
    }




    protected function getFormSchema(): array
    {
        return [

            Forms\Components\Grid::make(2)
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Forms\Components\Section::make('Purchase Info')
                        ->columnSpan(1)
                        ->schema([

                            Forms\Components\Select::make('product_supplier_id')
                                ->label('Supplier')
                                ->searchable()
                                ->preload()
                                ->relationship('supplier','name'),

                            Forms\Components\TextInput::make('purchase_invoice_id')
                                ->label('Purchase Invoice'),

                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->relationship('product','name')
                                ->searchable(['name','sku'])
                                ->live(),

                            MoneyInput::make('landing_cost'),




                        ]),


                    Forms\Components\Section::make('Stock Info')
                        ->columnSpan(1)
                        ->schema([

                            Forms\Components\TextInput::make('init_quantity')
                                ->required()
                                ->label(__('Stock Quantity'))
                                ->minValue(fn(?Model $record) =>  max(0, $record?->sold_quantity))
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
                                ->visible(fn(Forms\Get $get) => $get('product_id') && Product::find($get('product_id'))->type == ProductTypeCast::WHOLESALE)
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

        ];
    }



}
