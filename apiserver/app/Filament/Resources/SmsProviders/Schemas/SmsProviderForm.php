<?php

namespace App\Filament\Resources\SmsProviders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SmsProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('driver')
                    ->required(),
                Textarea::make('api_key')
                    ->columnSpanFull(),
                Textarea::make('api_secret')
                    ->columnSpanFull(),
                TextInput::make('sender_id'),
                TextInput::make('entity_id'),
                TextInput::make('config'),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('per_sms_cost')
                    ->required()
                    ->numeric()
                    ->default(0.25),
                TextInput::make('min_balance_threshold')
                    ->required()
                    ->numeric()
                    ->default(10.0),
                DateTimePicker::make('balance_checked_at'),
                DateTimePicker::make('rate_valid_until'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('supports_dlt')
                    ->required(),
                Toggle::make('supports_otp')
                    ->required(),
                Toggle::make('supports_promotional')
                    ->required(),
                Toggle::make('supports_whatsapp')
                    ->required(),
                Toggle::make('supports_voice_otp')
                    ->required(),
                TextInput::make('total_sent')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_delivered')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_failed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('success_rate')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                DateTimePicker::make('last_success_at'),
                DateTimePicker::make('last_failure_at'),
                TextInput::make('last_error'),
                TextInput::make('consecutive_failures')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
