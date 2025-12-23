<?php

namespace App\Filament\Resources\Mlm\MlmCommissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MlmCommissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('genealogy_id')
                    ->relationship('genealogy', 'id'),
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name'),
                TextInput::make('commissionable_type'),
                TextInput::make('commissionable_id')
                    ->numeric(),
                Select::make('type')
                    ->options([
                        'sponsor_bonus' => 'Sponsor bonus',
                        'level_commission' => 'Level commission',
                        'matching_bonus' => 'Matching bonus',
                        'level_achievement' => 'Level achievement',
                        'pool_bonus' => 'Pool bonus',
                        'purchase_commission' => 'Purchase commission',
                        'renewal_bonus' => 'Renewal bonus',
                        'adjustment' => 'Adjustment',
                        'reversal' => 'Reversal',
                    ])
                    ->default('level_commission')
                    ->required(),
                TextInput::make('level')
                    ->numeric(),
                TextInput::make('rate_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('base_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('gross_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tds_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('admin_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('net_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'held' => 'Held',
                        'cancelled' => 'Cancelled',
                        'reversed' => 'Reversed',
                    ])
                    ->default('pending')
                    ->required(),
                Select::make('paid_via_transaction_id')
                    ->relationship('paidViaTransaction', 'id'),
                DateTimePicker::make('paid_at'),
                DatePicker::make('commission_date')
                    ->required(),
                TextInput::make('period_key'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
                TextInput::make('approved_by')
                    ->numeric(),
                DateTimePicker::make('approved_at'),
                Select::make('reversed_commission_id')
                    ->relationship('reversedCommission', 'id'),
            ]);
    }
}
