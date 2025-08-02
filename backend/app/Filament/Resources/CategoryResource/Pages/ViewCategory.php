<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make('Category Image')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('display')->collection('displayImage'),
                    ]),

                Section::make('Main Info')
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ])->columnSpan(1)
                    ->schema([
                        TextEntry::make('parent.name')->label('Parent'),
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('url')->label('URL')
                            ->formatStateUsing(function ($record) {
                                return $record->url;
                            })
                            ->copyable()
                            ->copyableState(function ($record) {
                                return $record->url;
                            })
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),
                    ]),

                Section::make('Variable')
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ])
                    ->schema([
                        IconEntry::make('status')
                            ->boolean(),
                        IconEntry::make('is_visible_on_front')
                            ->boolean(),
                        TextEntry::make('view_count')->label('View Count'),
                        TextEntry::make('order')->label('Order'),
                    ]),

                Section::make('Date')
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ])
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),

                Section::make('Banners')
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ])
                    ->schema([
                        RepeatableEntry::make('banners')
                            ->schema([
                                TextEntry::make('link'),
                                SpatieMediaLibraryImageEntry::make('banner')->collection('bannerImage'),
                            ]),
                    ]),
            ]);
    }


}
