<?php

namespace App\Filament\Resources\Membership\Stages\Schemas;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                // =========================
                // Basic Info
                // =========================
                Section::make('Stage Information')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('uuid')
                                ->label('UUID')
                                ->required()
                                ->maxLength(36)
                                ->placeholder('Unique identifier'),

                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('e.g. Starter'),
                        ]),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('e.g. starter-stage'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Short description about this stage'),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Pricing
                // =========================
                Section::make('Pricing')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('base_price')
                                ->label('Base Price')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('discount')
                                ->label('Discount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('tax_percentage')
                                ->label('Tax %')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('tax_amount')
                                ->label('Tax Amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('price')
                                ->label('Final Price')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('$'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Matrix & Limits
                // =========================
                Section::make('Matrix & Limits')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('max_team_members')
                                ->label('Max Team Members')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(780),

                            TextInput::make('matrix_width')
                                ->label('Matrix Width')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(5),

                            TextInput::make('matrix_depth')
                                ->label('Matrix Depth')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(4),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Bonuses & Commissions
                // =========================
                Section::make('Bonuses & Commissions')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('matching_bonus_percent')
                                ->label('Matching Bonus %')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0.0),

                            TextInput::make('matching_bonus_levels')
                                ->label('Matching Bonus Levels')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1),

                            TextInput::make('pool_contribution_percent')
                                ->label('Pool Contribution %')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0.0),

                            TextInput::make('sponsor_bonus')
                                ->label('Sponsor Bonus')
                                ->numeric()
                                ->default(0),

                            TextInput::make('level_achievement_bonus')
                                ->label('Level Achievement Bonus')
                                ->numeric()
                                ->default(0),
                        ]),

                        Textarea::make('commission_rates')
                            ->label('Commission Rates')
                            ->rows(3)
                            ->placeholder('JSON / structured config')
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),

                // =========================
                // Upgrade & Values
                // =========================
                Section::make('Upgrade & Values')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            Select::make('upgrade_to_stage_id')
                                ->label('Upgrade To Stage')
                                ->relationship('upgradeToStage', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('None'),

                            TextInput::make('upgrade_price_difference')
                                ->label('Upgrade Price Difference')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('pv')
                                ->label('PV')
                                ->required()
                                ->numeric()
                                ->default(0),

                            TextInput::make('bv')
                                ->label('BV')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Misc & System
                // =========================
                Section::make('Miscellaneous')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('benefits')
                                ->label('Benefits')
                                ->placeholder('Optional'),

                            TextInput::make('accessibility')
                                ->label('Accessibility')
                                ->placeholder('Optional'),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->required()
                                ->numeric()
                                ->default(0),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->required(),

                            Toggle::make('is_default')
                                ->label('Default Stage')
                                ->required(),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),
            ])->columnSpanFull(),
        ]);
    }
}
