<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use App\Casts\IntegrationTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /* ---------------------------------------------
             | Message + Recipient
             ----------------------------------------------*/
            Section::make('Message')
                ->description('Recipient and message payload')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('uuid')
                        ->label('UUID')
                        ->disabled()
                        ->dehydrated()
                        ->hidden(fn (string $operation) => $operation === 'create')
                        ->helperText('System generated'),

                    TextInput::make('recipient')
                        ->required()
                        ->placeholder('+91XXXXXXXXXX')
                        ->helperText('E.164 recommended'),

                    Select::make('message_type')
                        ->label('Message Type')
                        ->options([
                            'otp' => 'OTP',
                            'transactional' => 'Transactional',
                            'promotional' => 'Promotional',
                            'alert' => 'Alert',
                        ])
                        ->default('transactional')
                        ->native(false)
                        ->required(),

                    TextInput::make('provider_slug')
                        ->label('Provider')
                        ->required()
                        ->placeholder('e.g. msg91 / twilio / etc'),

                    Textarea::make('message')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull()
                        ->placeholder('Full SMS body...'),
                ]),

            /* ---------------------------------------------
             | Template + Variables + User Context
             ----------------------------------------------*/
            Section::make('Context')
                ->description('Template, user, and loggable references')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    Select::make('integration_id')
                        ->label('Integration')
                        ->relationship(
                            'integration',
                            'name',
                            fn ($query) => $query->where('type', IntegrationTypeCast::SMS->value)
                        )
                        ->searchable()
                        ->preload()
                        ->placeholder('-'),

                    Select::make('sms_template_id')
                        ->label('Template')
                        ->relationship('template', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    TextInput::make('template_code')
                        ->label('Template Code')
                        ->placeholder('—'),

                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('—'),

                    TextInput::make('loggable_type')
                        ->label('Loggable Type')
                        ->placeholder('e.g. App\\Models\\Order')
                        ->disabled(fn (string $operation) => $operation !== 'create')
                        ->dehydrated(),

                    TextInput::make('loggable_id')
                        ->label('Loggable ID')
                        ->numeric()
                        ->placeholder('—')
                        ->disabled(fn (string $operation) => $operation !== 'create')
                        ->dehydrated(),

                    Textarea::make('variables')
                        ->label('Variables (JSON)')
                        ->rows(5)
                        ->columnSpanFull()
                        ->placeholder('{ "otp": "123456" }'),
                ]),

            /* ---------------------------------------------
             | Delivery + Status
             ----------------------------------------------*/
            Section::make('Delivery')
                ->description('Provider request identifiers and delivery state')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'queued' => 'Queued',
                            'sent' => 'Sent',
                            'delivered' => 'Delivered',
                            'failed' => 'Failed',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending')
                        ->native(false)
                        ->required(),

                    TextInput::make('delivery_status')
                        ->label('Delivery Status')
                        ->placeholder('—'),

                    TextInput::make('request_id')
                        ->label('Provider Request ID')
                        ->placeholder('—'),

                    TextInput::make('message_id')
                        ->label('Provider Message ID')
                        ->placeholder('—'),

                    DateTimePicker::make('sent_at')
                        ->seconds(false)
                        ->placeholder('—'),

                    DateTimePicker::make('delivered_at')
                        ->seconds(false)
                        ->placeholder('—'),

                    DateTimePicker::make('failed_at')
                        ->seconds(false)
                        ->placeholder('—'),
                ]),

            /* ---------------------------------------------
             | Cost + Segments + Retry
             ----------------------------------------------*/
            Section::make('Billing & Retry')
                ->description('Cost, segments, retry state')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('cost')
                        ->label('Cost')
                        ->required()
                        ->numeric()
                        ->default(0.0)
                        ->prefix('₹')
                        ->helperText('Stored as decimal'),

                    TextInput::make('segments')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->minValue(1),

                    TextInput::make('retry_count')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('max_retries')
                        ->required()
                        ->numeric()
                        ->default(3)
                        ->minValue(0),
                ]),

            /* ---------------------------------------------
             | Error + Debug
             ----------------------------------------------*/
            Section::make('Errors & Debug')
                ->description('Captured error and request context')
                ->collapsible()
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('error_code')
                        ->placeholder('—'),

                    TextInput::make('ip_address')
                        ->label('IP Address')
                        ->placeholder('—'),

                    TextInput::make('source')
                        ->placeholder('e.g. auth / order / cron')
                        ->helperText('Where this SMS was triggered from'),

                    TextInput::make('user_agent')
                        ->label('User Agent')
                        ->columnSpanFull()
                        ->placeholder('—'),

                    Textarea::make('error_message')
                        ->label('Error Message')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('—'),

                    Textarea::make('metadata')
                        ->label('Metadata (JSON)')
                        ->rows(6)
                        ->columnSpanFull()
                        ->placeholder('{ "provider_response": {...} }'),
                ]),
        ]);
    }
}
