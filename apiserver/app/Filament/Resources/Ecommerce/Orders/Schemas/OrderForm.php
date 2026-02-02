<?php

namespace App\Filament\Resources\Ecommerce\Orders\Schemas;

use App\Casts\OrderStatusCast;
use App\Services\MoneyService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $currency = MoneyService::make()->getCurrencyCode();

        return $schema->components([
            Flex::make([
                // LEFT
                Section::make([
                    Fieldset::make('Order')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextInput::make('uuid')
                                ->label('UUID')
                                ->required()
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('order_number')
                                ->label('Order #')
                                ->required()
                                ->disabled()
                                ->dehydrated(),

                            Select::make('status')
                                ->label('Status')
                                ->options(OrderStatusCast::class)
                                ->default('pending')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('tracking_id')
                                ->label('Tracking ID')
                                ->placeholder('-')
                                ->columnSpanFull(),

                            TextInput::make('voucher')
                                ->label('Voucher')
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),

                    Fieldset::make('Customer')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextInput::make('customerable_type')
                                ->label('Customer Type')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('customerable_id')
                                ->label('Customer ID')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(),
                        ]),
                ]),

                // RIGHT
                Section::make([
                    Fieldset::make('Payment & Flags')
                        ->schema([
                            Toggle::make('payment_success')
                                ->label('Payment Success')
                                ->required(),

                            Toggle::make('commission_processed')
                                ->label('Commission Processed')
                                ->required(),
                        ]),

                    Fieldset::make('Totals')
                        ->columns(2)
                        ->schema([
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix($currency),

                            TextInput::make('shipping_cost')
                                ->label('Shipping')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix($currency),

                            TextInput::make('tax')
                                ->label('Tax')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix($currency),

                            TextInput::make('discount')
                                ->label('Discount')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix($currency),

                            TextInput::make('quantity')
                                ->label('Total Qty')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('total')
                                ->label('Grand Total')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix($currency),
                        ]),

                    Fieldset::make('Rewards')
                        ->columns(2)
                        ->schema([
                            TextInput::make('total_bv')
                                ->label('Total BV')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('total_pv')
                                ->label('Total PV')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('total_reward_points')
                                ->label('Reward Points')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->columnSpanFull(),
                        ]),
                ])->grow(false),
            ])->from('md')->columnSpanFull(),

            Section::make('Addresses')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    Select::make('billing_address_id')
                        ->label('Billing Address')
                        ->relationship('billingAddress', 'title')
                        ->searchable()
                        ->placeholder('-'),

                    Select::make('shipping_address_id')
                        ->label('Shipping Address')
                        ->relationship('shippingAddress', 'title')
                        ->searchable()
                        ->placeholder('-'),
                ])
                ->columnSpanFull(),

            Section::make('Timeline')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    DateTimePicker::make('expire_at')->label('Expires At'),
                    DateTimePicker::make('delivered_at')->label('Delivered At'),
                    DateTimePicker::make('return_period_ends_at')->label('Return Ends At'),
                    DateTimePicker::make('completed_at')->label('Completed At'),
                ])
                ->columnSpanFull(),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(6)
                        ->columnSpanFull(),

                    Textarea::make('admin_notes')
                        ->label('Admin Notes')
                        ->rows(6)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }
}
