<?php

namespace App\Filament\Resources\Addresses\Schemas;

use App\Models\Address;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AddressInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'md' => 12,
            ])->schema([
                // =========================
                // LEFT (Main) - md:8
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 8])
                    ->schema([
                        Section::make('Contact')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('uuid')
                                        ->label('UUID')
                                        ->copyable()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('title')
                                        ->label('Title')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('person_name')
                                        ->label('Person Name')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('person_mobile')
                                        ->label('Mobile')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('person_email')
                                        ->label('Email')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('alternate_contact')
                                        ->label('Alternate Contact')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('type')
                                        ->label('Type')
                                        ->badge()
                                        ->columnSpan(['md' => 12])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Address')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                TextEntry::make('address_1')
                                    ->label('Address Line 1')
                                    ->columnSpanFull()
                                    ->placeholder('-'),

                                TextEntry::make('address_2')
                                    ->label('Address Line 2')
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('landmark')
                                        ->label('Landmark')
                                        ->placeholder('-')
                                        ->columnSpan(['md' => 6]),

                                    TextEntry::make('pickup_location')
                                        ->label('Pickup Location')
                                        ->placeholder('-')
                                        ->columnSpan(['md' => 6]),

                                    TextEntry::make('city')
                                        ->label('City')
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('postal_code')
                                        ->label('Postal Code')
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('block.name')
                                        ->label('Block')
                                        ->placeholder('-')
                                        ->columnSpan(['md' => 4]),

                                    TextEntry::make('district.name')
                                        ->label('District')
                                        ->placeholder('-')
                                        ->columnSpan(['md' => 4]),
                                ]),
                            ])
                            ->compact()
                            ->collapsible(),
                    ]),

                // =========================
                // RIGHT (Sidebar) - md:4
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 4])
                    ->schema([
                        Section::make('Region & Geo')
                            ->icon('heroicon-o-globe-asia-australia')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('state_code')
                                        ->label('State Code')
                                        ->placeholder('-'),

                                    TextEntry::make('country_code')
                                        ->label('Country Code')
                                        ->placeholder('-'),

                                    TextEntry::make('latitude')
                                        ->label('Latitude')
                                        ->numeric()
                                        ->placeholder('-'),

                                    TextEntry::make('longitude')
                                        ->label('Longitude')
                                        ->numeric()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact()
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Owner Mapping')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('addressable_type')
                                        ->label('Owner Type')
                                        ->placeholder('-'),

                                    TextEntry::make('addressable_id')
                                        ->label('Owner ID')
                                        ->numeric()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact()
                            ->collapsible(),

                        Section::make('Settings')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Grid::make(1)->schema([
                                    IconEntry::make('default')
                                        ->label('Default')
                                        ->boolean(),

                                    TextEntry::make('priority')
                                        ->label('Priority')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('System')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Updated')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('deleted_at')
                                        ->label('Deleted')
                                        ->dateTime()
                                        ->visible(fn (Address $record): bool => $record->trashed())
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsed()
                            ->collapsible()
                            ->compact(),
                    ]),
            ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'gap-6']),
        ]);
    }
}
