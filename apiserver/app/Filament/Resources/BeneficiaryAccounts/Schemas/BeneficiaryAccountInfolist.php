<?php

namespace App\Filament\Resources\BeneficiaryAccounts\Schemas;

use App\Models\BeneficiaryAccount;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BeneficiaryAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('accountable_type'),
                TextEntry::make('accountable_id')
                    ->numeric(),
                TextEntry::make('wallet.id')
                    ->label('Wallet')
                    ->placeholder('-'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('account_number')
                    ->placeholder('-'),
                TextEntry::make('ifsc_code')
                    ->placeholder('-'),
                TextEntry::make('bank_name')
                    ->placeholder('-'),
                TextEntry::make('branch_name')
                    ->placeholder('-'),
                TextEntry::make('upi_id')
                    ->placeholder('-'),
                TextEntry::make('holder_name'),
                TextEntry::make('integration.name')
                    ->label('Integration')
                    ->placeholder('-'),
                TextEntry::make('provider_beneficiary_id')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (BeneficiaryAccount $record): bool => $record->trashed()),
            ]);
    }
}
