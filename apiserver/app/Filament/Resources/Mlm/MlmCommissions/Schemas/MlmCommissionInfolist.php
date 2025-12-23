<?php

namespace App\Filament\Resources\Mlm\MlmCommissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MlmCommissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('genealogy.id')
                    ->numeric(),
                TextEntry::make('fromUser.name')
                    ->numeric(),
                TextEntry::make('commissionable_type'),
                TextEntry::make('commissionable_id')
                    ->numeric(),
                TextEntry::make('type'),
                TextEntry::make('level')
                    ->numeric(),
                TextEntry::make('rate_percent')
                    ->numeric(),
                TextEntry::make('base_amount')
                    ->numeric(),
                TextEntry::make('gross_amount')
                    ->numeric(),
                TextEntry::make('tds_amount')
                    ->numeric(),
                TextEntry::make('admin_fee')
                    ->numeric(),
                TextEntry::make('net_amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('paidViaTransaction.id')
                    ->numeric(),
                TextEntry::make('paid_at')
                    ->dateTime(),
                TextEntry::make('commission_date')
                    ->date(),
                TextEntry::make('period_key'),
                TextEntry::make('approved_by')
                    ->numeric(),
                TextEntry::make('approved_at')
                    ->dateTime(),
                TextEntry::make('reversedCommission.id')
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
