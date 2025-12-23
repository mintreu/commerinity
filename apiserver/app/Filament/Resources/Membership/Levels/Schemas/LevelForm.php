<?php

namespace App\Filament\Resources\Membership\Levels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('global_rank')
                    ->required()
                    ->numeric(),
                TextInput::make('level_number')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('team_member_limit')
                    ->required()
                    ->numeric()
                    ->default(5),
                TextInput::make('min_direct_referrals')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_active_directs')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_personal_purchase')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_team_sales')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('validity_days')
                    ->required()
                    ->numeric()
                    ->default(365),
                TextInput::make('joining_bonus')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('purchase_commission')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('recruitment_commission')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('depth_commissions'),
                TextInput::make('commission_multiplier')
                    ->required()
                    ->numeric()
                    ->default(1.0),
                TextInput::make('level_benefits'),
                TextInput::make('badge_icon'),
                TextInput::make('badge_color'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
