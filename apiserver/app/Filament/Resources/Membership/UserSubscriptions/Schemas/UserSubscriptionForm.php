<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Subscription')
                ->id('user-subscription-form-tabs')
                ->persistTab()
                ->columnSpanFull()
                ->tabs([
                    /* -------------------------
                     | Core
                     --------------------------*/
                    Tab::make('Core')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Identity')
                                ->description('User + Stage/Level + Status')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextInput::make('uuid')
                                        ->label('UUID')
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('System generated identifier')
                                        ->hidden(fn (string $operation) => $operation === 'create'),

                                    Select::make('user_id')
                                        ->label('User')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('stage_id')
                                        ->label('Stage')
                                        ->relationship('stage', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($set) => $set('level_id', null)),

                                    Select::make('level_id')
                                        ->label('Level')
                                        ->relationship(
                                            name: 'level',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn ($query, $get) =>
                                            $query->when(
                                                $get('stage_id'),
                                                fn ($q, $stageId) => $q->where('stage_id', $stageId)
                                            )->orderBy('level_number')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'pending' => 'Pending',
                                            'active' => 'Active',
                                            'expired' => 'Expired',
                                            'cancelled' => 'Cancelled',
                                            'upgraded' => 'Upgraded',
                                        ])
                                        ->default('pending')
                                        ->native(false)
                                        ->required(),
                                ]),

                            Section::make('Subscription Period')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    DateTimePicker::make('starts_at')
                                        ->seconds(false),

                                    DateTimePicker::make('expires_at')
                                        ->seconds(false),
                                ]),
                        ]),

                    /* -------------------------
                     | Progress
                     --------------------------*/
                    Tab::make('Progress')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->schema([
                            Section::make('Level Progress')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    Select::make('current_level_id')
                                        ->label('Current Level')
                                        ->relationship('currentLevel', 'name')
                                        ->searchable()
                                        ->preload(),

                                    Select::make('highest_level_id')
                                        ->label('Highest Level')
                                        ->relationship('highestLevel', 'name')
                                        ->searchable()
                                        ->preload(),

                                    DateTimePicker::make('level_achieved_at')
                                        ->label('Level Achieved At')
                                        ->seconds(false)
                                        ->columnSpanFull(),

                                    Textarea::make('qualification_snapshot')
                                        ->label('Qualification Snapshot (JSON)')
                                        ->rows(6)
                                        ->columnSpanFull()
                                        ->placeholder('{ "rule": "...", "stats": {...} }'),
                                ]),

                            Section::make('PV Metrics')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextInput::make('personal_pv')
                                        ->required()
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('team_pv')
                                        ->required()
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ]),
                        ]),

                    /* -------------------------
                     | Billing
                     --------------------------*/
                    Tab::make('Billing')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Section::make('Pricing (stored as integer paisa)')
                                ->description('If you want ₹ input but store paisa, tell me — I’ll wire hydrate/dehydrate.')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextInput::make('base_price')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('discount')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('tax_amount')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('amount')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0)
                                        ->helperText('Final payable amount'),
                                ]),

                            Section::make('Renewal')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    DateTimePicker::make('last_renewed_at')
                                        ->seconds(false),

                                    TextInput::make('renewal_count')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),

                                    Select::make('previous_subscription_id')
                                        ->label('Previous Subscription')
                                        ->relationship('previousSubscription', 'uuid')
                                        ->searchable()
                                        ->preload(),
                                ]),
                        ]),

                    /* -------------------------
                     | Payment & Commission
                     --------------------------*/
                    Tab::make('Payment')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Section::make('Payment')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    Toggle::make('is_paid')
                                        ->label('Paid')
                                        ->required()
                                        ->inline(false)
                                        ->live(),

                                    DateTimePicker::make('paid_at')
                                        ->label('Paid At')
                                        ->seconds(false)
                                        ->hidden(fn ($get) => ! (bool) $get('is_paid')),

                                    Select::make('transaction_id')
                                        ->label('Transaction')
                                        ->relationship('transaction', 'uuid')
                                        ->searchable()
                                        ->preload(),

                                    Select::make('wallet_id')
                                        ->label('Wallet')
                                        ->relationship('wallet', 'uuid')
                                        ->searchable()
                                        ->preload(),
                                ]),

                            Section::make('Commission')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextInput::make('total_commission_earned')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('current_month_commission')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0),
                                ]),
                        ]),

                    /* -------------------------
                     | Sponsor + Metadata
                     --------------------------*/
                    Tab::make('Sponsor & Notes')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Sponsor (Morph Owner)')
                                ->description('Who paid for this subscription (sponsor_type + sponsor_id)')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextInput::make('sponsor_type')
                                        ->label('Sponsor Type')
                                        ->placeholder('e.g. App\\Models\\User'),

                                    TextInput::make('sponsor_id')
                                        ->label('Sponsor ID')
                                        ->numeric()
                                        ->placeholder('—'),
                                ]),

                            Section::make('Metadata')
                                ->schema([
                                    Textarea::make('metadata')
                                        ->label('Metadata (JSON)')
                                        ->rows(6)
                                        ->columnSpanFull()
                                        ->placeholder('{ "source": "web", "coupon": null }'),

                                    Textarea::make('notes')
                                        ->rows(4)
                                        ->columnSpanFull()
                                        ->placeholder('Internal notes…'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
