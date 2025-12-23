<?php

namespace App\Filament\Resources\Membership\Levels\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LevelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('stage.name')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('full_name'),
                TextEntry::make('global_rank')
                    ->numeric(),
                TextEntry::make('level_number')
                    ->numeric(),
                TextEntry::make('slug'),
                TextEntry::make('team_member_limit')
                    ->numeric(),
                TextEntry::make('min_direct_referrals')
                    ->numeric(),
                TextEntry::make('min_active_directs')
                    ->numeric(),
                TextEntry::make('min_personal_purchase')
                    ->numeric(),
                TextEntry::make('min_team_sales')
                    ->numeric(),
                TextEntry::make('validity_days')
                    ->numeric(),
                TextEntry::make('joining_bonus')
                    ->numeric(),
                TextEntry::make('purchase_commission')
                    ->numeric(),
                TextEntry::make('recruitment_commission')
                    ->numeric(),
                TextEntry::make('commission_multiplier')
                    ->numeric(),
                TextEntry::make('badge_icon'),
                TextEntry::make('badge_color'),
                TextEntry::make('sort_order')
                    ->numeric(),
                IconEntry::make('is_active')
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
