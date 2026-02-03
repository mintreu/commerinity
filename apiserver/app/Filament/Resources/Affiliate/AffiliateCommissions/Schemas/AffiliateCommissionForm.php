<?php

namespace App\Filament\Resources\Affiliate\AffiliateCommissions\Schemas;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AffiliateCommissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /* -------------------------------------------------
             | Identity & Parties
             -------------------------------------------------*/
            Section::make('Commission')
                ->description('Receiver, source user, type and status')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('uuid')
                        ->label('UUID')
                        ->disabled()
                        ->dehydrated()
                        ->hidden(fn (string $operation) => $operation === 'create')
                        ->helperText('System generated'),

                    Select::make('user_id')
                        ->label('Receiver')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('from_user_id')
                        ->label('Triggered By')
                        ->relationship('fromUser', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    Select::make('genealogy_id')
                        ->label('Genealogy')
                        ->relationship('genealogy', 'id')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    Select::make('type')
                        ->label('Commission Type')
                        ->options(CommissionTypeCast::class)
                        ->native(false)
                        ->default(CommissionTypeCast::LEVEL_COMMISSION->value)
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(CommissionStatusCast::class)
                        ->native(false)
                        ->default(CommissionStatusCast::PENDING->value)
                        ->required(),
                ]),

            /* -------------------------------------------------
             | Rate & Level
             -------------------------------------------------*/
            Section::make('Rate & Level')
                ->description('Level depth and % used for calculation')
                ->columns(['default' => 1, 'md' => 3])
                ->schema([
                    TextInput::make('level')
                        ->label('Level (Depth)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(50)
                        ->placeholder('e.g. 1'),

                    TextInput::make('rate_percent')
                        ->label('Rate %')
                        ->numeric()
                        ->step('0.01')
                        ->default(0.00)
                        ->required(),

                    TextInput::make('base_amount')
                        ->label('Base Amount (paisa)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->helperText('Base on which commission is calculated'),
                ]),

            /* -------------------------------------------------
             | Amounts
             -------------------------------------------------*/
            Section::make('Amounts')
                ->description('Ledger amounts (stored in paisa). Net = Gross - TDS - Admin Fee')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('gross_amount')
                        ->label('Gross (paisa)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $gross = (int) ($get('gross_amount') ?? 0);
                            $tds   = (int) ($get('tds_amount') ?? 0);
                            $fee   = (int) ($get('admin_fee') ?? 0);
                            $set('net_amount', max(0, $gross - $tds - $fee));
                        }),

                    TextInput::make('tds_amount')
                        ->label('TDS (paisa)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $gross = (int) ($get('gross_amount') ?? 0);
                            $tds   = (int) ($get('tds_amount') ?? 0);
                            $fee   = (int) ($get('admin_fee') ?? 0);
                            $set('net_amount', max(0, $gross - $tds - $fee));
                        }),

                    TextInput::make('admin_fee')
                        ->label('Admin Fee (paisa)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $gross = (int) ($get('gross_amount') ?? 0);
                            $tds   = (int) ($get('tds_amount') ?? 0);
                            $fee   = (int) ($get('admin_fee') ?? 0);
                            $set('net_amount', max(0, $gross - $tds - $fee));
                        }),

                    TextInput::make('net_amount')
                        ->label('Net (paisa)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Auto-calculated'),
                ]),

            /* -------------------------------------------------
             | Payment & Dates
             -------------------------------------------------*/
            Section::make('Payment & Dates')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    DatePicker::make('commission_date')
                        ->label('Commission Date')
                        ->required(),

                    TextInput::make('period_key')
                        ->label('Period Key')
                        ->placeholder('YYYY-MM e.g. 2026-02'),

                    Select::make('paid_via_transaction_id')
                        ->label('Paid via Transaction')
                        ->relationship('paidViaTransaction', 'id')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    DateTimePicker::make('paid_at')
                        ->label('Paid At')
                        ->seconds(false)
                        ->placeholder('—'),
                ]),

            /* -------------------------------------------------
             | Approval & Reversal
             -------------------------------------------------*/
            Section::make('Approval & Reversal')
                ->description('Admin approval and reversal linkage')
                ->columns(['default' => 1, 'md' => 2])
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('approved_by')
                        ->label('Approved By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    DateTimePicker::make('approved_at')
                        ->label('Approved At')
                        ->seconds(false)
                        ->placeholder('—'),

                    Select::make('reversed_commission_id')
                        ->label('Reversed Commission')
                        ->relationship('reversedCommission', 'id')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),
                ]),

            /* -------------------------------------------------
             | Description & Metadata
             -------------------------------------------------*/
            Section::make('Notes')
                ->columns(['default' => 1, 'md' => 1])
                ->schema([
                    Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Internal description / audit note...'),

                    Textarea::make('metadata')
                        ->label('Metadata (JSON)')
                        ->rows(6)
                        ->columnSpanFull()
                        ->placeholder('{ "key": "value" }')
                        ->helperText('Stored as array in DB'),
                ]),

            /* -------------------------------------------------
             | Commissionable (Internal)
             -------------------------------------------------*/
            Section::make('Commissionable Reference')
                ->description('Internal polymorphic linkage (usually set by system)')
                ->collapsible()
                ->collapsed()
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('commissionable_type')
                        ->label('Commissionable Type')
                        ->placeholder('e.g. App\\Models\\Membership\\UserSubscription'),

                    TextInput::make('commissionable_id')
                        ->label('Commissionable ID')
                        ->numeric()
                        ->placeholder('—'),

                    TextInput::make('idempotency_key')
                        ->label('Idempotency Key')
                        ->columnSpanFull()
                        ->placeholder('—'),
                ]),
        ]);
    }
}
