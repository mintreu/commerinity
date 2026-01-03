<?php

namespace App\Filament\Resources\Integrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Textarea::make('credentials')
                    ->columnSpanFull(),
                TextInput::make('settings'),
                Toggle::make('is_sandbox')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                DateTimePicker::make('last_tested_at'),
                TextInput::make('last_test_result'),
                Textarea::make('last_test_message')
                    ->columnSpanFull(),
            ]);
    }
}
