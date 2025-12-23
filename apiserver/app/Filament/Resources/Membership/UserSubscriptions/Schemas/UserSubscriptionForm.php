<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('stage_id')
                    ->required()
                    ->numeric(),
                TextInput::make('level_id')
                    ->required()
                    ->numeric(),
                TextInput::make('current_level_id')
                    ->numeric(),
                DateTimePicker::make('level_achieved_at'),
                TextInput::make('highest_level_id')
                    ->numeric(),
                TextInput::make('qualification_snapshot'),
                TextInput::make('personal_pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('team_pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_commission_earned')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('current_month_commission')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_renewed_at'),
                TextInput::make('renewal_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_paid')
                    ->required(),
                DateTimePicker::make('paid_at'),
                TextInput::make('transaction_id')
                    ->numeric(),
                TextInput::make('wallet_id')
                    ->numeric(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('previous_subscription_id')
                    ->numeric(),
                TextInput::make('originator_type'),
                TextInput::make('originator_id')
                    ->numeric(),
                TextInput::make('metadata'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
