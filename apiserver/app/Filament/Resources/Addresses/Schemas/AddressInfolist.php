<?php

namespace App\Filament\Resources\Addresses\Schemas;

use App\Models\Address;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AddressInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('person_name'),
                TextEntry::make('person_email')
                    ->placeholder('-'),
                TextEntry::make('person_mobile'),
                TextEntry::make('alternate_contact')
                    ->placeholder('-'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('address_1')
                    ->columnSpanFull(),
                TextEntry::make('address_2')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('landmark')
                    ->placeholder('-'),
                TextEntry::make('city'),
                TextEntry::make('postal_code'),
                TextEntry::make('block.name')
                    ->label('Block')
                    ->placeholder('-'),
                TextEntry::make('state_code')
                    ->placeholder('-'),
                TextEntry::make('country_code'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('default')
                    ->boolean(),
                TextEntry::make('priority')
                    ->numeric(),
                TextEntry::make('pickup_location')
                    ->placeholder('-'),
                TextEntry::make('addressable_type')
                    ->placeholder('-'),
                TextEntry::make('addressable_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Address $record): bool => $record->trashed()),
            ]);
    }
}
