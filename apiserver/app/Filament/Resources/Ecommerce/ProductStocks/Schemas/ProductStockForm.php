<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Schemas;

use App\Services\MoneyService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Purchase Info')
//                    ->aside()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('supplier_id')
                            ->columnSpan(2)
                            ->relationship('supplier', 'name'),
                        TextInput::make('purchase_invoice_number'),

                        Grid::make(2)->schema([
                            DatePicker::make('purchase_date'),
                            DatePicker::make('expiry_date'),
                            ])->columnSpan(2),
                        TextInput::make('batch_number'),
                    ]),



                Section::make('Stock Detail')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disabled()
                            ->columnSpan(2)
                            ->required(),
                        TextInput::make('init_quantity')
                            ->required()
                            ->numeric(),
                        TextInput::make('sold_quantity')
                            ->numeric()
                            ->disabled()
                            ->default(0),
                        TextInput::make('in_stock_quantity')
                            ->disabled()
                            ->numeric(),

                        TextInput::make('priority')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Toggle::make('in_stock')->inlineLabel()->inline()->disabled(),
                    ]),


                Section::make('Shop Config')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('landing_cost')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('INR'),
                        TextInput::make('profit_margin')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix(MoneyService::make(0)->getCurrency()),


                        TextInput::make('min_quantity')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('max_quantity')
                            ->numeric(),
                        TextInput::make('wholesale_unit_quantity')
                            ->numeric(),


                        TextInput::make('bv')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('pv')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('reward_points')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_commissionable')
                            ->live()
                            ->required(),

                        TextInput::make('commission_rate')
                            ->visible(fn(Get $get) => $get('is_commissionable'))
                            ->inlineLabel()
                            ->numeric(),


                    ]),







                Section::make('Configuration')
                    ->aside()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([

                        Select::make('address_id')
                            ->label('Warehouse Address')
                            ->relationship('address', 'title')
                            ->columnSpanFull(),

                        Toggle::make('notify_on_low_stock')
                            ->live()
                            ->required(),

                        TextInput::make('low_stock_threshold')
                            ->columnSpan(2)
                            ->required()
                            ->label('Threshold Stock')
                            ->numeric()
                            ->inlineLabel()
                            ->visible(fn(Get $get) => $get('notify_on_low_stock'))
                            ->default(5),

                        Textarea::make('notes')
                            ->columnSpanFull(),

                    ])





            ]);
    }
}
