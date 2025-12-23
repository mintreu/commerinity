<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('wallet.id')
                    ->numeric(),
                TextEntry::make('transactionable_type'),
                TextEntry::make('transactionable_id')
                    ->numeric(),
                TextEntry::make('type'),
                TextEntry::make('status'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('fee')
                    ->numeric(),
                TextEntry::make('tax')
                    ->numeric(),
                TextEntry::make('net_amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('payment_method'),
                TextEntry::make('integration.name')
                    ->numeric(),
                TextEntry::make('provider_order_id'),
                TextEntry::make('provider_transaction_id'),
                TextEntry::make('provider_signature'),
                TextEntry::make('checkout_url'),
                TextEntry::make('qr_code_url'),
                IconEntry::make('is_verified')
                    ->boolean(),
                TextEntry::make('verified_at')
                    ->dateTime(),
                TextEntry::make('description'),
                TextEntry::make('purpose'),
                TextEntry::make('reference_number'),
                TextEntry::make('parentTransaction.id')
                    ->numeric(),
                TextEntry::make('expires_at')
                    ->dateTime(),
                TextEntry::make('balance_after')
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
