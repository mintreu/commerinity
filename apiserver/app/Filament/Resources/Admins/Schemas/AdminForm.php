<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Casts\AdminStatusCast;
use App\Casts\AdminTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
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
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('mobile'),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('type')
                    ->options(AdminTypeCast::class)
                    ->default('executive')
                    ->required(),
                Select::make('status')
                    ->options(AdminStatusCast::class)
                    ->default('active')
                    ->required(),
                TextInput::make('created_by_admin_id')
                    ->numeric(),
                TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->default(5),
                TextInput::make('profit_share_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('profit_share_active')
                    ->required(),
                TextInput::make('locale')
                    ->required()
                    ->default('en'),
                TextInput::make('preferences'),
                TextInput::make('two_factor_secret'),
                Toggle::make('two_factor_enabled')
                    ->required(),
                DateTimePicker::make('last_login_at'),
                TextInput::make('last_login_ip'),
            ]);
    }
}
