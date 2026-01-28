<?php

namespace App\Filament\Resources\Ecommerce\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([

                    SpatieMediaLibraryImageEntry::make('thumbnail')->collection('thumbnail')->imageSize('400px'),

                    Section::make('Info')
                        ->columnSpan(2)
                        ->columns(1)
                        ->schema([
                            TextEntry::make('name')->size(TextSize::Large)->weight(FontWeight::ExtraBold),
                            TextEntry::make('slug')
                                ->placeholder('-'),
                            TextEntry::make('url')
                                ->placeholder('-'),

                            TextEntry::make('parent_id')
                                ->numeric()
                                ->placeholder('-'),

                            IconEntry::make('status')
                                ->boolean(),
                        ]),


                    Section::make('About')
                        ->columnSpanFull()
                        ->schema([
                            TextEntry::make('desc')
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),

                    TextEntry::make('view_count')
                        ->numeric(),
                    TextEntry::make('order')
                        ->numeric(),

                    TextEntry::make('created_at')
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->dateTime()
                        ->placeholder('-'),



                ])->columns(3)->columnSpanFull(),

            ]);
    }
}
