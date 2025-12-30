<?php

namespace App\Filament\Resources\Ecommerce\Orders\Schemas;

use App\Casts\OrderStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('customerable_type'),
                TextInput::make('customerable_id')
                    ->numeric(),
                Select::make('status')
                    ->options(OrderStatusCast::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_bv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_reward_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('commission_processed')
                    ->required(),
                Select::make('shipping_address_id')
                    ->relationship('shippingAddress', 'title'),
                Select::make('billing_address_id')
                    ->relationship('billingAddress', 'title'),
                DateTimePicker::make('expire_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('return_period_ends_at'),
                DateTimePicker::make('completed_at'),
                TextInput::make('voucher'),
                TextInput::make('tracking_id'),
                Toggle::make('payment_success')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
