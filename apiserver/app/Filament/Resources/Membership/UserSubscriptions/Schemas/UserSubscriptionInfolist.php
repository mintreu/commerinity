<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('stage_id')
                    ->numeric(),
                TextEntry::make('level_id')
                    ->numeric(),
                TextEntry::make('current_level_id')
                    ->numeric(),
                TextEntry::make('level_achieved_at')
                    ->dateTime(),
                TextEntry::make('highest_level_id')
                    ->numeric(),
                TextEntry::make('personal_pv')
                    ->numeric(),
                TextEntry::make('team_pv')
                    ->numeric(),
                TextEntry::make('total_commission_earned')
                    ->numeric(),
                TextEntry::make('current_month_commission')
                    ->numeric(),
                TextEntry::make('last_renewed_at')
                    ->dateTime(),
                TextEntry::make('renewal_count')
                    ->numeric(),
                TextEntry::make('base_price')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('tax_amount')
                    ->numeric(),
                TextEntry::make('amount')
                    ->numeric(),
                IconEntry::make('is_paid')
                    ->boolean(),
                TextEntry::make('paid_at')
                    ->dateTime(),
                TextEntry::make('transaction_id')
                    ->numeric(),
                TextEntry::make('wallet_id')
                    ->numeric(),
                TextEntry::make('starts_at')
                    ->dateTime(),
                TextEntry::make('expires_at')
                    ->dateTime(),
                TextEntry::make('status'),
                TextEntry::make('previous_subscription_id')
                    ->numeric(),
                TextEntry::make('originator_type'),
                TextEntry::make('originator_id')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
