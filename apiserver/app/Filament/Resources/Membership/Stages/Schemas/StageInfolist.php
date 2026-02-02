<?php

namespace App\Filament\Resources\Membership\Stages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StageInfolist
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
                        Section::make('Stage Overview')
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

                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('slug')
                                        ->label('Slug')
                                        ->columnSpan(['md' => 12])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Pricing')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('base_price')
                                        ->label('Base Price')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('discount')
                                        ->label('Discount')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('tax_percentage')
                                        ->label('Tax %')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('tax_amount')
                                        ->label('Tax Amount')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 3])
                                        ->placeholder('-'),

                                    TextEntry::make('price')
                                        ->label('Final Price')
                                        ->money()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('upgrade_price_difference')
                                        ->label('Upgrade Price Difference')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->compact(),

                        Section::make('Matrix & Limits')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('max_team_members')
                                        ->label('Max Team Members')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('matrix_width')
                                        ->label('Matrix Width')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('matrix_depth')
                                        ->label('Matrix Depth')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 4])
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
                        Section::make('Bonuses')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('matching_bonus_percent')
                                        ->label('Matching Bonus %')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('matching_bonus_levels')
                                        ->label('Matching Bonus Levels')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('pool_contribution_percent')
                                        ->label('Pool Contribution %')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('pv')
                                        ->label('PV')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('bv')
                                        ->label('BV')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Upgrade & Status')
                            ->icon('heroicon-o-arrow-up-circle')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('upgradeToStage.name')
                                        ->label('Upgrade To Stage')
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

                                    IconEntry::make('is_default')
                                        ->label('Default')
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
