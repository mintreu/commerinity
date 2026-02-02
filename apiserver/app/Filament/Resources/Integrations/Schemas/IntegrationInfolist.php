<?php

namespace App\Filament\Resources\Integrations\Schemas;

use App\Models\Integration;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntegrationInfolist
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
                        Section::make('Integration')
                            ->icon('heroicon-o-puzzle-piece')
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

                                    TextEntry::make('type')
                                        ->label('Type')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('slug')
                                        ->label('Slug')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Credentials')
                            ->icon('heroicon-o-key')
                            ->schema([
                                KeyValueEntry::make('credentials'),
                                TextEntry::make('credentials')
                                    ->label('Credentials')
                                    ->placeholder('-')
                                    ->columnSpanFull()
                                    ->formatStateUsing(function ($state): string {
                                        if (empty($state) || ! is_array($state)) {
                                            return '-';
                                        }

                                        // readable json, but keep it short-ish
                                        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                        return $json ?: '-';
                                    }),
                            ])
                            ->collapsible()
                            ->compact(),
                    ]),

                // =========================
                // RIGHT (Sidebar) - md:4
                // =========================
                Grid::make(1)
                    ->columnSpan(['md' => 4])
                    ->schema([
                        Section::make('Status')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Grid::make(1)->schema([
                                    IconEntry::make('is_active')
                                        ->label('Active')
                                        ->boolean(),

                                    IconEntry::make('is_sandbox')
                                        ->label('Sandbox')
                                        ->boolean(),

                                    IconEntry::make('is_default')
                                        ->label('Default')
                                        ->boolean(),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Health')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextEntry::make('last_tested_at')
                                        ->label('Last Tested')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('last_test_result')
                                        ->label('Last Test Result')
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('last_test_message')
                                        ->label('Last Test Message')
                                        ->placeholder('-')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->collapsible()
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
                                        ->visible(fn (Integration $record): bool => $record->trashed())
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
