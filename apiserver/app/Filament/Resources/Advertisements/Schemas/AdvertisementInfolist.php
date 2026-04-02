<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Configuration')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('name'),
                        TextEntry::make('slug')
                            ->copyable(),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('placement')
                            ->badge(),
                        TextEntry::make('position_type')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('page_target')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('page_pattern')
                            ->placeholder('-'),
                        TextEntry::make('block')
                            ->placeholder('-'),
                        TextEntry::make('position')
                            ->numeric(),
                    ]),
                ]),

            Section::make('Visibility & Timing')
                ->schema([
                    Grid::make(4)->schema([
                        IconEntry::make('is_active')
                            ->boolean(),
                        IconEntry::make('is_premium')
                            ->boolean(),
                        IconEntry::make('show_to_guests')
                            ->boolean(),
                        IconEntry::make('show_to_members')
                            ->boolean(),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('starts_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('ends_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('target_user_types')
                            ->badge()
                            ->formatStateUsing(function ($state): array {
                                if (! is_array($state)) {
                                    return [];
                                }

                                return array_values(array_filter(array_map('strval', $state)));
                            })
                            ->placeholder('-'),
                    ]),
                ]),

            Section::make('Content')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('title')
                            ->placeholder('-'),
                        TextEntry::make('link_text')
                            ->placeholder('-'),
                    ]),
                    TextEntry::make('description')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('link_url')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    IconEntry::make('open_in_new_tab')
                        ->boolean(),
                ]),

            Section::make('Third-party')
                ->collapsed()
                ->collapsible()
                ->schema([
                    TextEntry::make('ad_unit_id')
                        ->placeholder('-'),
                    TextEntry::make('third_party_script_url')
                        ->placeholder('-'),
                    TextEntry::make('affiliate_network')
                        ->placeholder('-'),
                    TextEntry::make('affiliate_tracking_id')
                        ->placeholder('-'),
                    TextEntry::make('ad_code')
                        ->label('Ad Code')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    KeyValueEntry::make('third_party_config')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    KeyValueEntry::make('position_config')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('Analytics')
                ->collapsed()
                ->collapsible()
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('impressions')
                            ->numeric(),
                        TextEntry::make('target_views')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('clicks')
                            ->numeric(),
                        TextEntry::make('last_impression_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('last_click_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
                ]),
        ]);
    }
}
