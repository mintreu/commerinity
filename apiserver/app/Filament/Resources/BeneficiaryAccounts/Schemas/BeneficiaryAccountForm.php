<?php

namespace App\Filament\Resources\BeneficiaryAccounts\Schemas;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\BeneficiaryTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BeneficiaryAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('accountable_type')
                    ->required(),
                TextInput::make('accountable_id')
                    ->required()
                    ->numeric(),
                Select::make('wallet_id')
                    ->relationship('wallet', 'id'),
                Select::make('type')
                    ->options(BeneficiaryTypeCast::class)
                    ->required(),
                TextInput::make('account_number'),
                TextInput::make('ifsc_code'),
                TextInput::make('bank_name'),
                TextInput::make('branch_name'),
                TextInput::make('upi_id'),
                TextInput::make('holder_name')
                    ->required(),
                Select::make('integration_id')
                    ->relationship('integration', 'name'),
                TextInput::make('provider_beneficiary_id'),
                Select::make('status')
                    ->options(BeneficiaryStatusCast::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('verified_at'),
                Toggle::make('is_default')
                    ->required(),
                TextInput::make('metadata'),
            ]);
    }
}
