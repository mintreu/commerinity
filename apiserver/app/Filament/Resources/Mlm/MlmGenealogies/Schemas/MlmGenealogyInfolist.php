<?php

namespace App\Filament\Resources\Mlm\MlmGenealogies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MlmGenealogyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('placementParent.name')
                    ->numeric(),
                TextEntry::make('placement_position')
                    ->numeric(),
                TextEntry::make('depth')
                    ->numeric(),
                TextEntry::make('direct_count')
                    ->numeric(),
                TextEntry::make('active_direct_count')
                    ->numeric(),
                TextEntry::make('level_1_count')
                    ->numeric(),
                TextEntry::make('level_2_count')
                    ->numeric(),
                TextEntry::make('level_3_count')
                    ->numeric(),
                TextEntry::make('level_4_count')
                    ->numeric(),
                TextEntry::make('total_team_count')
                    ->numeric(),
                TextEntry::make('active_team_count')
                    ->numeric(),
                TextEntry::make('personal_sales')
                    ->numeric(),
                TextEntry::make('level_1_sales')
                    ->numeric(),
                TextEntry::make('level_2_sales')
                    ->numeric(),
                TextEntry::make('level_3_sales')
                    ->numeric(),
                TextEntry::make('level_4_sales')
                    ->numeric(),
                TextEntry::make('total_team_sales')
                    ->numeric(),
                TextEntry::make('personal_pv')
                    ->numeric(),
                TextEntry::make('team_pv')
                    ->numeric(),
                TextEntry::make('currentStage.name')
                    ->numeric(),
                TextEntry::make('currentLevel.name')
                    ->numeric(),
                TextEntry::make('highestLevel.name')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('activated_at')
                    ->dateTime(),
                TextEntry::make('last_activity_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
