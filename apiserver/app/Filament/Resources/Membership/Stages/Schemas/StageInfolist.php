<?php

namespace App\Filament\Resources\Membership\Stages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('base_price')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('tax_percentage')
                    ->numeric(),
                TextEntry::make('tax_amount')
                    ->numeric(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('max_team_members')
                    ->numeric(),
                TextEntry::make('matrix_width')
                    ->numeric(),
                TextEntry::make('matrix_depth')
                    ->numeric(),
                TextEntry::make('matching_bonus_percent')
                    ->numeric(),
                TextEntry::make('matching_bonus_levels')
                    ->numeric(),
                TextEntry::make('pool_contribution_percent')
                    ->numeric(),
                TextEntry::make('upgradeToStage.name')
                    ->numeric(),
                TextEntry::make('upgrade_price_difference')
                    ->numeric(),
                TextEntry::make('pv')
                    ->numeric(),
                TextEntry::make('bv')
                    ->numeric(),
                TextEntry::make('sort_order')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
