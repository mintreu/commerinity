<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Details')
                    ->tabs([
                        Tab::make('Profile')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    Section::make('Account Overview')
                                        ->schema([
                                            SpatieMediaLibraryImageEntry::make('avatar')
                                                ->collection('avatar')
                                                ->circular(),
                                            TextEntry::make('name')
                                                ->weight('bold')
                                                ->size('lg'),
                                            TextEntry::make('email')
                                                ->icon('heroicon-m-envelope')
                                                ->copyable(),
                                            TextEntry::make('mobile')
                                                ->icon('heroicon-m-phone')
                                                ->copyable(),
                                        ])->columnSpan(1),

                                    Section::make('System Identification')
                                        ->schema([
                                            TextEntry::make('uuid')
                                                ->label('Internal UUID')
                                                ->copyable(),
                                            TextEntry::make('referral_code')
                                                ->label('Referral Code')
                                                ->weight('bold')
                                                ->color('primary')
                                                ->copyable(),
                                            TextEntry::make('type')
                                                ->badge(),
                                            TextEntry::make('status')
                                                ->badge(),
                                        ])->columnSpan(1),
                                ]),

                                Section::make('Biography')
                                    ->schema([
                                        TextEntry::make('bio')
                                            ->placeholder('No bio provided')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Personal Info')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Section::make('Personal Details')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('gender')
                                                ->badge(),
                                            TextEntry::make('dob')
                                                ->label('Date of Birth')
                                                ->date(),
                                            IconEntry::make('onboarded')
                                                ->boolean()
                                                ->label('Onboarding Status'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Business Hub')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make('Hierarchy & Growth')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('parent.name')
                                                ->label('Upline/Referrer')
                                                ->placeholder('Direct Registration'),
                                            TextEntry::make('level_id')
                                                ->label('Current Rank/Level')
                                                ->numeric(),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextEntry::make('subscribed_at')
                                                ->dateTime()
                                                ->placeholder('Not Subscribed'),
                                            TextEntry::make('created_at')
                                                ->label('Member Since')
                                                ->dateTime(),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->persistTab()
                    ->id('user-infolist-tabs')
                    ->columnSpanFull(),
            ]);
    }
}
