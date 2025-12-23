<?php

namespace App\Filament\Resources\Wallets\Schemas;

use App\Casts\WalletStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('walletable_type')
                    ->required(),
                TextInput::make('walletable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('hold_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_credited')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_debited')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('pin'),
                DateTimePicker::make('pin_updated_at'),
                TextInput::make('currency')
                    ->required()
                    ->default('INR'),
                Select::make('status')
                    ->options(WalletStatusCast::class)
                    ->default('active')
                    ->required(),
                TextInput::make('metadata'),
            ]);
    }
}
