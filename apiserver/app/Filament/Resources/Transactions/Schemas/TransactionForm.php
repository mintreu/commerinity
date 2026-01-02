<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('wallet_id')
                    ->relationship('wallet', 'id'),
                TextInput::make('transactionable_type'),
                TextInput::make('transactionable_id')
                    ->numeric(),
                Select::make('type')
                    ->options(TransactionTypeCast::class)
                    ->required(),
                Select::make('status')
                    ->options(TransactionStatusCast::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('net_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('currency')
                    ->required()
                    ->default('INR'),
                Select::make('payment_method')
                    ->options(PaymentMethodCast::class),
                Select::make('integration_id')
                    ->relationship('integration', 'name'),
                TextInput::make('provider_order_id'),
                TextInput::make('provider_transaction_id'),
                TextInput::make('provider_signature'),
                TextInput::make('checkout_url'),
                TextInput::make('qr_code_url'),
                Toggle::make('verified')
                    ->required(),
                DateTimePicker::make('verified_at'),
                TextInput::make('description'),
                TextInput::make('purpose'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('reference_number'),
                Select::make('parent_transaction_id')
                    ->relationship('parentTransaction', 'id'),
                DateTimePicker::make('expires_at'),
                TextInput::make('balance_after')
                    ->numeric(),
                TextInput::make('metadata'),
                TextInput::make('provider_response'),
            ]);
    }
}
