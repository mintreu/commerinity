<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SmsTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sms_provider_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('message_id')
                    ->required(),
                TextInput::make('entity_id'),
                TextInput::make('template_id'),
                TextInput::make('sender_id')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('variables'),
                TextInput::make('variable_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('category')
                    ->required()
                    ->default('transactional'),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_dlt_approved')
                    ->required(),
                DateTimePicker::make('dlt_approved_at'),
                TextInput::make('usage_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_used_at'),
            ]);
    }
}
