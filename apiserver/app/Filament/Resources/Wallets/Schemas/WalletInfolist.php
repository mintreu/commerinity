<?php

namespace App\Filament\Resources\Wallets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('walletable_type'),
                TextEntry::make('walletable_id')
                    ->numeric(),
                TextEntry::make('balance')
                    ->numeric(),
                TextEntry::make('hold_balance')
                    ->numeric(),
                TextEntry::make('total_credited')
                    ->numeric(),
                TextEntry::make('total_debited')
                    ->numeric(),
                TextEntry::make('points')
                    ->numeric(),
                TextEntry::make('pin'),
                TextEntry::make('pin_updated_at')
                    ->dateTime(),
                TextEntry::make('currency'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
