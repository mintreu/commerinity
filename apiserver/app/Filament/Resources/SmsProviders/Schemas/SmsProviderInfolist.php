<?php

namespace App\Filament\Resources\SmsProviders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SmsProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('driver'),
                TextEntry::make('sender_id'),
                TextEntry::make('entity_id'),
                TextEntry::make('balance')
                    ->numeric(),
                TextEntry::make('per_sms_cost')
                    ->numeric(),
                TextEntry::make('min_balance_threshold')
                    ->numeric(),
                TextEntry::make('balance_checked_at')
                    ->dateTime(),
                TextEntry::make('rate_valid_until')
                    ->dateTime(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('priority')
                    ->numeric(),
                IconEntry::make('supports_dlt')
                    ->boolean(),
                IconEntry::make('supports_otp')
                    ->boolean(),
                IconEntry::make('supports_promotional')
                    ->boolean(),
                IconEntry::make('supports_whatsapp')
                    ->boolean(),
                IconEntry::make('supports_voice_otp')
                    ->boolean(),
                TextEntry::make('total_sent')
                    ->numeric(),
                TextEntry::make('total_delivered')
                    ->numeric(),
                TextEntry::make('total_failed')
                    ->numeric(),
                TextEntry::make('success_rate')
                    ->numeric(),
                TextEntry::make('last_success_at')
                    ->dateTime(),
                TextEntry::make('last_failure_at')
                    ->dateTime(),
                TextEntry::make('last_error'),
                TextEntry::make('consecutive_failures')
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
