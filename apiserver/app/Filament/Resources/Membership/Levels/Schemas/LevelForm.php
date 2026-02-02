<?php

namespace App\Filament\Resources\Membership\Levels\Schemas;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                Section::make('Basic Info')
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
                                ->placeholder('Auto / Unique')
                                ->readOnly(fn (string $operation): bool => $operation === 'edit'),

                            Select::make('stage_id')
                                ->label('Stage')
                                ->relationship('stage', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Select stage')
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record) => $record->name ?: ('Stage #' . $record->getKey())
                                ),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(80)
                                ->placeholder('e.g. Silver'),

                            TextInput::make('full_name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('e.g. Silver Member'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('global_rank')
                                ->label('Global Rank')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('0'),

                            TextInput::make('level_number')
                                ->label('Level Number')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->placeholder('1'),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->placeholder('0'),
                        ]),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('e.g. silver-member'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Short description for this level...'),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Limits & Requirements')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('team_member_limit')
                                ->label('Team Member Limit')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(5),

                            TextInput::make('validity_days')
                                ->label('Validity (Days)')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(365),

                            TextInput::make('min_direct_referrals')
                                ->label('Min Direct Referrals')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('min_active_directs')
                                ->label('Min Active Directs')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('min_personal_purchase')
                                ->label('Min Personal Purchase')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('min_team_sales')
                                ->label('Min Team Sales')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                Section::make('Commissions & Bonuses')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('joining_bonus')
                                ->label('Joining Bonus')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0.0)
                                ->placeholder('0.00'),

                            TextInput::make('purchase_commission')
                                ->label('Purchase Commission')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0.0)
                                ->placeholder('0.00'),

                            TextInput::make('recruitment_commission')
                                ->label('Recruitment Commission')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0.0)
                                ->placeholder('0.00'),

                            TextInput::make('commission_multiplier')
                                ->label('Commission Multiplier')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(1.0)
                                ->placeholder('1.00'),
                        ]),

                        Textarea::make('depth_commissions')
                            ->label('Depth Commissions')
                            ->rows(3)
                            ->placeholder('JSON / config string...')
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),

                Section::make('Benefits & Badge')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Textarea::make('level_benefits')
                            ->label('Level Benefits')
                            ->rows(3)
                            ->placeholder('Benefits list / JSON / notes...')
                            ->columnSpanFull(),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('badge_icon')
                                ->label('Badge Icon')
                                ->placeholder('e.g. heroicon-o-star'),

                            Select::make('badge_color')
                                ->label('Badge Color')
                                ->options([
                                    'gray' => 'Gray',
                                    'primary' => 'Primary',
                                    'success' => 'Success',
                                    'warning' => 'Warning',
                                    'danger' => 'Danger',
                                    'info' => 'Info',
                                ])
                                ->placeholder('Select color'),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->required()
                                ->default(true),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),
            ])->columnSpanFull(),
        ]);
    }
}
