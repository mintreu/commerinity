<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /* -------------------------------------------------
             | Message Overview
             -------------------------------------------------*/
            Section::make('Message')
                ->description('Recipient and message payload')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('uuid')
                        ->label('UUID')
                        ->copyable(),

                    TextEntry::make('recipient')
                        ->label('Recipient'),

                    TextEntry::make('message_type')
                        ->label('Type')
                        ->badge(),

                    TextEntry::make('provider_slug')
                        ->label('Provider'),

                    TextEntry::make('message')
                        ->label('Message Body')
                        ->columnSpanFull()
                        ->limit(300),
                ]),

            /* -------------------------------------------------
             | Context
             -------------------------------------------------*/
            Section::make('Context')
                ->description('Template and ownership context')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('provider.name')
                        ->label('Provider Record')
                        ->placeholder('—'),

                    TextEntry::make('template.name')
                        ->label('Template')
                        ->placeholder('—'),

                    TextEntry::make('template_code')
                        ->label('Template Code')
                        ->placeholder('—'),

                    TextEntry::make('user.name')
                        ->label('User')
                        ->placeholder('—'),

                    TextEntry::make('loggable_type')
                        ->label('Loggable Type')
                        ->placeholder('—'),

                    TextEntry::make('loggable_id')
                        ->label('Loggable ID')
                        ->numeric()
                        ->placeholder('—'),
                ]),

            /* -------------------------------------------------
             | Delivery Status
             -------------------------------------------------*/
            Section::make('Delivery')
                ->description('Provider delivery state')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('status')
                        ->badge(),

                    TextEntry::make('delivery_status')
                        ->label('Delivery Status')
                        ->placeholder('—'),

                    TextEntry::make('request_id')
                        ->label('Request ID')
                        ->placeholder('—'),

                    TextEntry::make('message_id')
                        ->label('Message ID')
                        ->placeholder('—'),

                    TextEntry::make('sent_at')
                        ->label('Sent At')
                        ->dateTime()
                        ->placeholder('—'),

                    TextEntry::make('delivered_at')
                        ->label('Delivered At')
                        ->dateTime()
                        ->placeholder('—'),

                    TextEntry::make('failed_at')
                        ->label('Failed At')
                        ->dateTime()
                        ->placeholder('—'),
                ]),

            /* -------------------------------------------------
             | Cost & Retry
             -------------------------------------------------*/
            Section::make('Cost & Retry')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('cost')
                        ->label('Cost')
                        ->formatStateUsing(fn ($state) => '₹'.number_format((float) $state, 4)),

                    TextEntry::make('segments')
                        ->numeric(),

                    TextEntry::make('retry_count')
                        ->numeric(),

                    TextEntry::make('max_retries')
                        ->numeric(),
                ]),

            /* -------------------------------------------------
             | Errors & Debug
             -------------------------------------------------*/
            Section::make('Errors & Debug')
                ->collapsible()
                ->collapsed()
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('error_code')
                        ->placeholder('—'),

                    TextEntry::make('error_message')
                        ->columnSpanFull()
                        ->placeholder('—'),

                    TextEntry::make('ip_address')
                        ->label('IP Address')
                        ->placeholder('—'),

                    TextEntry::make('source')
                        ->placeholder('—'),

                    TextEntry::make('user_agent')
                        ->label('User Agent')
                        ->columnSpanFull()
                        ->placeholder('—'),
                ]),

            /* -------------------------------------------------
             | Audit
             -------------------------------------------------*/
            Section::make('Audit')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextEntry::make('created_at')
                        ->dateTime(),

                    TextEntry::make('updated_at')
                        ->dateTime(),
                ]),
        ]);
    }
}
