<?php

namespace App\Filament\Resources\Membership\Levels\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'md' => 12,
            ])->schema([
                // =========================
                // LEFT (Main) - md:8
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 8])
                    ->schema([
                        Section::make('Level Overview')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('uuid')
                                        ->label('UUID')
                                        ->copyable()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('stage.name')
                                        ->label('Stage')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('full_name')
                                        ->label('Full Name')
                                        ->columnSpan(['md' => 8])
                                        ->placeholder('-'),

                                    TextEntry::make('slug')
                                        ->label('Slug')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('level_number')
                                        ->label('Level No.')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('global_rank')
                                        ->label('Global Rank')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Requirements')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('team_member_limit')
                                        ->label('Team Limit')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('validity_days')
                                        ->label('Validity (Days)')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('min_direct_referrals')
                                        ->label('Min Direct Referrals')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('min_active_directs')
                                        ->label('Min Active Directs')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('min_personal_purchase')
                                        ->label('Min Personal Purchase')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('min_team_sales')
                                        ->label('Min Team Sales')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->compact(),
                    ]),

                // =========================
                // RIGHT (Sidebar) - md:4
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 4])
                    ->schema([
                        Section::make('Commissions')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 1, // sidebar: single column on desktop
                                ])->schema([
                                    TextEntry::make('joining_bonus')
                                        ->label('Joining Bonus')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('purchase_commission')
                                        ->label('Purchase Commission')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('recruitment_commission')
                                        ->label('Recruitment Commission')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('commission_multiplier')
                                        ->label('Multiplier')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Badge & Status')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('badge_icon')
                                        ->label('Badge Icon')
                                        ->placeholder('-'),

                                    TextEntry::make('badge_color')
                                        ->label('Badge Color')
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('sort_order')
                                        ->label('Sort Order')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    IconEntry::make('is_active')
                                        ->label('Active')
                                        ->boolean(),
                                ]),
                            ])
                            ->compact(),

                        Section::make('System')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Updated')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('deleted_at')
                                        ->label('Deleted')
                                        ->dateTime()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsed()
                            ->compact(),
                    ]),
            ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'gap-6']),
        ]);
    }
}
