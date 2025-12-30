<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AffiliateGenealogyForm
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
                Select::make('placement_parent_id')
                    ->relationship('placementParent', 'name'),
                TextInput::make('placement_position')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('depth')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('direct_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('active_direct_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_1_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_2_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_3_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_4_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_team_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('active_team_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('personal_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_1_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_2_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_3_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('level_4_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_team_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('personal_pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('team_pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('current_stage_id')
                    ->relationship('currentStage', 'name'),
                Select::make('current_level_id')
                    ->relationship('currentLevel', 'name'),
                Select::make('highest_level_id')
                    ->relationship('highestLevel', 'name'),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('activated_at'),
                DateTimePicker::make('last_activity_at'),
            ]);
    }
}
