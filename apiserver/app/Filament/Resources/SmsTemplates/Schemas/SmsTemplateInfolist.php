<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SmsTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sms_provider_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('message_id'),
                TextEntry::make('entity_id'),
                TextEntry::make('template_id'),
                TextEntry::make('sender_id'),
                TextEntry::make('variable_count')
                    ->numeric(),
                TextEntry::make('category'),
                TextEntry::make('language'),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_dlt_approved')
                    ->boolean(),
                TextEntry::make('dlt_approved_at')
                    ->dateTime(),
                TextEntry::make('usage_count')
                    ->numeric(),
                TextEntry::make('last_used_at')
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
