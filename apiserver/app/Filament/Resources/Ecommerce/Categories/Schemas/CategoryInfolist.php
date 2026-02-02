<?php

namespace App\Filament\Resources\Ecommerce\Categories\Schemas;

use App\Filament\Resources\Ecommerce\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Model;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Flex::make([
                // LEFT SIDEBAR (fixed / not growing)
                Section::make()
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('thumbnail')
                            ->hiddenLabel()
                            ->collection('thumbnail')
                            ->imageSize('420px')
                            ->extraImgAttributes([
                                'class' => 'rounded-xl object-cover',
                            ])
                            ->placeholder('-'),
                    ])
                    ->grow(false),

                // RIGHT CONTENT (grows)
                Group::make([
                    Section::make('Info')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label('Name')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::ExtraBold)
                                ->columnSpanFull(),

                            TextEntry::make('url')
                                ->label('URL')
                                ->placeholder('-')
                                ->hintAction(
                                    Action::make('visit')
                                        ->label('Visit')
                                        ->url(
                                            fn (Model $record) => rtrim(config('app.client_url'), '/') . '/category/' . $record->url,
                                            true
                                        )
                                ),

                            TextEntry::make('parent_id')
                                ->label('Parent ID')
                                ->numeric()
                                ->placeholder('-'),

                            IconEntry::make('status')
                                ->label('Status')
                                ->boolean(),
                        ]),

                    Section::make('About')
                        ->schema([
                            TextEntry::make('desc')
                                ->hiddenLabel()
                                ->placeholder('-')
                                ->html()
                                ->alignJustify()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Meta')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('view_count')
                                ->label('Views')
                                ->numeric()
                                ->placeholder('-'),

                            TextEntry::make('order')
                                ->label('Order')
                                ->numeric()
                                ->placeholder('-'),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime()
                                ->placeholder('-'),

                            TextEntry::make('updated_at')
                                ->label('Updated')
                                ->dateTime()
                                ->placeholder('-'),
                        ]),
                ]),
            ])->from('md')->columnSpanFull(), // docs: md+ e side-by-side, smaller e stack
            // CHILDREN
            Section::make('Children')
                ->collapsible()
                ->schema([
                    RepeatableEntry::make('children')
                        ->table([
                            RepeatableEntry\TableColumn::make('name'),
                            RepeatableEntry\TableColumn::make('url'),
                            RepeatableEntry\TableColumn::make('status'),
                        ])
                        ->schema([
                            TextEntry::make('name'),

                            TextEntry::make('url')
                                ->placeholder('-')
                                ->hintAction(
                                    Action::make('visit')
                                        ->label('Visit')
                                        // repeatable item state is the URL string
                                        ->url(
                                            fn (Model $record, ?string $state) => $state
                                                ? rtrim(config('app.client_url'), '/') . '/category/' . $state
                                                : null,
                                            true
                                        )
                                        ->visible(fn (?string $state) => filled($state))
                                ),

                            IconEntry::make('status')->boolean(),

                            Actions::make([
                                Action::make('view')->url(fn(Model $record, CategoryResource $resource) => $resource::getUrl('view',['record' => $record->url]))
                            ])->alignCenter(),


                        ])
                        ->placeholder('-'),
                ])
                ->columnSpanFull(),
        ]);
    }
}
