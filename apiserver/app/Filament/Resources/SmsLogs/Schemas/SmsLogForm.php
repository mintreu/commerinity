<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SmsLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('sms_provider_id')
                    ->numeric(),
                TextInput::make('provider_slug')
                    ->required(),
                TextInput::make('recipient')
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('message_type')
                    ->required()
                    ->default('transactional'),
                TextInput::make('sms_template_id')
                    ->numeric(),
                TextInput::make('template_code'),
                TextInput::make('variables'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('loggable_type'),
                TextInput::make('loggable_id')
                    ->numeric(),
                TextInput::make('request_id'),
                TextInput::make('message_id'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('delivery_status'),
                DateTimePicker::make('sent_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('failed_at'),
                TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('segments')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('error_code'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                TextInput::make('retry_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_retries')
                    ->required()
                    ->numeric()
                    ->default(3),
                TextInput::make('ip_address'),
                TextInput::make('user_agent'),
                TextInput::make('source'),
                TextInput::make('metadata'),
            ]);
    }
}
