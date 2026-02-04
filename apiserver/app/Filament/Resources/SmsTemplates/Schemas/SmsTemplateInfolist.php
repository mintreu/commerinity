<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsTemplateInfolist
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
                        Section::make('Template')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('slug')
                                        ->label('Slug')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('message_id')
                                        ->label('Message ID')
                                        ->copyable()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('sender_id')
                                        ->label('Sender ID')
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('entity_id')
                                        ->label('Entity ID')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('template_id')
                                        ->label('Template ID')
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->compact(),

                        Section::make('Classification')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 12,
                                ])->schema([
                                    TextEntry::make('integration.name')
                                        ->label('Integration')
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('category')
                                        ->label('Category')
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('language')
                                        ->label('Language')
                                        ->badge()
                                        ->columnSpan(['md' => 4])
                                        ->placeholder('-'),

                                    TextEntry::make('variable_count')
                                        ->label('Variable Count')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),

                                    TextEntry::make('usage_count')
                                        ->label('Usage Count')
                                        ->numeric()
                                        ->badge()
                                        ->columnSpan(['md' => 6])
                                        ->placeholder('-'),
                                ]),
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

                                    IconEntry::make('is_dlt_approved')
                                        ->label('DLT Approved')
                                        ->boolean(),

                                    TextEntry::make('dlt_approved_at')
                                        ->label('DLT Approved At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('last_used_at')
                                        ->label('Last Used At')
                                        ->dateTime()
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
