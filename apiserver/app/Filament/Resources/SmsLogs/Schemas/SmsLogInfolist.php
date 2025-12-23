<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SmsLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('sms_provider_id')
                    ->numeric(),
                TextEntry::make('provider_slug'),
                TextEntry::make('recipient'),
                TextEntry::make('message_type'),
                TextEntry::make('sms_template_id')
                    ->numeric(),
                TextEntry::make('template_code'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('loggable_type'),
                TextEntry::make('loggable_id')
                    ->numeric(),
                TextEntry::make('request_id'),
                TextEntry::make('message_id'),
                TextEntry::make('status'),
                TextEntry::make('delivery_status'),
                TextEntry::make('sent_at')
                    ->dateTime(),
                TextEntry::make('delivered_at')
                    ->dateTime(),
                TextEntry::make('failed_at')
                    ->dateTime(),
                TextEntry::make('cost')
                    ->money(),
                TextEntry::make('segments')
                    ->numeric(),
                TextEntry::make('error_code'),
                TextEntry::make('retry_count')
                    ->numeric(),
                TextEntry::make('max_retries')
                    ->numeric(),
                TextEntry::make('ip_address'),
                TextEntry::make('user_agent'),
                TextEntry::make('source'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
