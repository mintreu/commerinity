<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('mobile'),
                DateTimePicker::make('mobile_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('referral_code'),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('level_id')
                    ->numeric(),
                TextInput::make('originator_type'),
                TextInput::make('originator_id')
                    ->numeric(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                Select::make('gender')
                    ->options(GenderCast::class)
                    ->default('other')
                    ->required(),
                DatePicker::make('dob'),
                Select::make('type')
                    ->options(UserTypeCast::class)
                    ->default('regular')
                    ->required(),
                Select::make('status')
                    ->options(UserStatusCast::class)
                    ->default('draft')
                    ->required(),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),
                Toggle::make('onboarded')
                    ->required(),
                DateTimePicker::make('subscribed_at'),
            ]);
    }
}
