<?php

namespace App\Filament\Resources\Ecommerce\Sales\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('starts_from')
                    ->dateTime(),
                TextEntry::make('ends_till')
                    ->dateTime(),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('condition_type')
                    ->badge(),
                IconEntry::make('end_other_rules')
                    ->boolean(),
                TextEntry::make('action_type')
                    ->badge(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
