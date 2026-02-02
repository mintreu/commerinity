<?php

namespace App\Filament\Resources\Ecommerce\Categories\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Flex::make([
                // LEFT: Main content
                Group::make([
                    Section::make('Category Details')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            Select::make('parent_id')
                                ->label('Parent Category')
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Root (no parent)')
                                ->columnSpanFull(),

                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(120)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, callable $set) {
                                    // normalize if user edits manually
                                    $set('url', filled($state) ? Str::slug($state) : null);
                                })
                                ->columnSpanFull(),

                            TextInput::make('url')
                                ->label('Slug')
                                ->helperText('Auto-generated from name. You can edit it.')
                                ->required()
                                ->maxLength(160)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, callable $set) {
                                    // normalize if user edits manually
                                    $set('url', filled($state) ? Str::slug($state) : null);
                                })
                                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Str::slug($state) : null)
                                ->unique(ignoreRecord: true)
                                ->prefix('/')
                                ->columnSpanFull(),

                            Textarea::make('desc')
                                ->label('Description')
                                ->rows(8)
                                ->placeholder('Write a short category description...')
                                ->columnSpanFull(),
                        ]),

                    Section::make('SEO & Metadata')
                        ->collapsible()
                        ->collapsed()
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            KeyValue::make('seo_meta')
                                ->label('SEO Meta')
                                ->keyLabel('Key')
                                ->valueLabel('Value')
                                ->addActionLabel('Add SEO field')
                                ->columnSpanFull(),

                            KeyValue::make('meta_data')
                                ->label('Extra Meta')
                                ->keyLabel('Key')
                                ->valueLabel('Value')
                                ->addActionLabel('Add meta field')
                                ->columnSpanFull(),
                        ]),
                ]),

                // RIGHT: Controls + Media
                Group::make([
                    Section::make('Publishing')
                        ->columns(2)
                        ->schema([
                            Toggle::make('status')
                                ->label('Active')
                                ->default(true)
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('order')
                                ->label('Priority')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),

                            TextInput::make('view_count')
                                ->label('Views')
                                ->numeric()
                                ->default(0)
                                ->disabled() // usually system-driven
                                ->dehydrated() // keep saving existing value
                                ->helperText('Auto tracked by system.'),
                        ]),

                    Section::make('Media')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('thumbnail')
                                ->label('Thumbnail')
                                ->collection('thumbnail')
                                ->image()
                                ->maxFiles(1)
                                ->helperText('Recommended: 800×800 (square).')
                                ->downloadable()
                                ->openable(),

                            SpatieMediaLibraryFileUpload::make('banners')
                                ->label('Banners')
                                ->collection('bannerImage')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->helperText('Recommended: 1600×500 (wide).')
                                ->downloadable()
                                ->openable(),
                        ]),
                ])->grow(false),
            ])->from('md')->columnSpanFull(),
        ]);
    }
}
