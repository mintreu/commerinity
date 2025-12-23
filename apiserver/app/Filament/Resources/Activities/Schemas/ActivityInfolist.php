<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('log_name')
                            ->label('Log Type')
                            ->badge(),

                        TextEntry::make('event')
                            ->label('Event')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'login' => 'success',
                                'logout' => 'warning',
                                'page_view' => 'info',
                                'action' => 'primary',
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        TextEntry::make('causer.name')
                            ->label('User'),

                        TextEntry::make('causer.email')
                            ->label('Email'),

                        TextEntry::make('created_at')
                            ->label('Time')
                            ->dateTime('M j, Y H:i:s'),

                        TextEntry::make('batch_uuid')
                            ->label('Batch UUID')
                            ->placeholder('N/A'),
                    ]),

                Section::make('Properties')
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('properties')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
