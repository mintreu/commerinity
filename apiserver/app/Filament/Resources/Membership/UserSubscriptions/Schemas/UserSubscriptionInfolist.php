<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Subscription Details')
                ->id('user-subscription-infolist-tabs')
                ->persistTab()
                ->columnSpanFull()
                ->tabs([
                    /* -------------------------
                     | Overview
                     --------------------------*/
                    Tab::make('Overview')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Identity')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('uuid')
                                        ->label('UUID')
                                        ->copyable(),

                                    TextEntry::make('user.name')
                                        ->label('User'),

                                    TextEntry::make('stage.name')
                                        ->label('Stage'),

                                    TextEntry::make('level.name')
                                        ->label('Initial Level'),

                                    TextEntry::make('status')
                                        ->badge(),

                                    IconEntry::make('is_paid')
                                        ->label('Paid')
                                        ->boolean(),
                                ]),

                            Section::make('Subscription Period')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('starts_at')
                                        ->dateTime()
                                        ->placeholder('—'),

                                    TextEntry::make('expires_at')
                                        ->dateTime()
                                        ->placeholder('—'),
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
                                    TextEntry::make('currentLevel.name')
                                        ->label('Current Level')
                                        ->placeholder('—'),

                                    TextEntry::make('highestLevel.name')
                                        ->label('Highest Level')
                                        ->placeholder('—'),

                                    TextEntry::make('level_achieved_at')
                                        ->label('Level Achieved At')
                                        ->dateTime()
                                        ->placeholder('—'),
                                ]),

                            Section::make('PV Metrics')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('personal_pv')
                                        ->numeric(),

                                    TextEntry::make('team_pv')
                                        ->numeric(),
                                ]),
                        ]),

                    /* -------------------------
                     | Billing
                     --------------------------*/
                    Tab::make('Billing')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Section::make('Pricing (Stored in Paisa)')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('base_price')
                                        ->label('Base Price'),

                                    TextEntry::make('discount')
                                        ->label('Discount'),

                                    TextEntry::make('tax_amount')
                                        ->label('Tax'),

                                    TextEntry::make('amount')
                                        ->label('Final Amount')
                                        ->weight('bold'),
                                ]),

                            Section::make('Renewal')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('renewal_count')
                                        ->numeric(),

                                    TextEntry::make('last_renewed_at')
                                        ->dateTime()
                                        ->placeholder('—'),

                                    TextEntry::make('previousSubscription.uuid')
                                        ->label('Previous Subscription')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
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
                                    TextEntry::make('paid_at')
                                        ->label('Paid At')
                                        ->dateTime()
                                        ->placeholder('—'),

                                    TextEntry::make('transaction.id')
                                        ->label('Transaction ID')
                                        ->placeholder('—'),

                                    TextEntry::make('wallet.name')
                                        ->label('Wallet')
                                        ->placeholder('—'),
                                ]),

                            Section::make('Commission')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('total_commission_earned')
                                        ->label('Total Commission'),

                                    TextEntry::make('current_month_commission')
                                        ->label('Current Month Commission'),
                                ]),
                        ]),

                    /* -------------------------
                     | System
                     --------------------------*/
                    Tab::make('System')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Sponsor')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('sponsor_type')
                                        ->label('Sponsor Type')
                                        ->placeholder('—'),

                                    TextEntry::make('sponsor_id')
                                        ->label('Sponsor ID')
                                        ->numeric()
                                        ->placeholder('—'),
                                ]),

                            Section::make('Audit')
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->dateTime(),

                                    TextEntry::make('updated_at')
                                        ->dateTime(),

                                    TextEntry::make('deleted_at')
                                        ->dateTime()
                                        ->placeholder('—'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
