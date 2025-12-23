<?php

namespace App\Filament\Resources\Membership\Stages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('max_team_members')
                    ->required()
                    ->numeric()
                    ->default(780),
                TextInput::make('matrix_width')
                    ->required()
                    ->numeric()
                    ->default(5),
                TextInput::make('matrix_depth')
                    ->required()
                    ->numeric()
                    ->default(4),
                TextInput::make('commission_rates'),
                TextInput::make('sponsor_bonus'),
                TextInput::make('matching_bonus_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('matching_bonus_levels')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('pool_contribution_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('level_achievement_bonus'),
                Select::make('upgrade_to_stage_id')
                    ->relationship('upgradeToStage', 'name'),
                TextInput::make('upgrade_price_difference')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('bv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('benefits'),
                TextInput::make('accessibility'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }
}
