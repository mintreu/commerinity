<?php

namespace App\Filament\Resources\Integrations\Schemas;

use App\Models\Integration;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IntegrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('type'),
                TextEntry::make('credentials')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_sandbox')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('last_tested_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_test_result')
                    ->placeholder('-'),
                TextEntry::make('last_test_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Integration $record): bool => $record->trashed()),
            ]);
    }
}
