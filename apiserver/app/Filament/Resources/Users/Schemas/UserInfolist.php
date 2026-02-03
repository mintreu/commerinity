<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('User Details')
                ->id('user-infolist-tabs')
                ->persistTab()
                ->columnSpanFull()
                ->tabs([


                    Tab::make('Profile')
                        ->icon('heroicon-m-user-circle')
                        ->schema([
                            Grid::make([
                                'default' => 1,
                                'lg' => 3,
                            ])->schema([
                                // Left: Profile card
                                Section::make('Account Overview')
                                    ->description('Primary identity & contact')
                                    ->columnSpan([
                                        'default' => 1,
                                        'lg' => 1,
                                    ])
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('avatar')
                                            ->collection('avatar')
                                            ->circular()
                                            ->imageSize(96),

                                        TextEntry::make('name')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->placeholder('—'),

                                        TextEntry::make('email')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable()
                                            ->placeholder('—'),

                                        TextEntry::make('mobile')
                                            ->icon('heroicon-m-phone')
                                            ->copyable()
                                            ->placeholder('—'),
                                    ]),

                                // Right: System identifiers
                                Section::make('System Identification')
                                    ->description('Internal & program attributes')
                                    ->columnSpan([
                                        'default' => 1,
                                        'lg' => 2,
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextEntry::make('uuid')
                                            ->label('Internal UUID')
                                            ->copyable()
                                            ->placeholder('—'),

                                        TextEntry::make('referral_code')
                                            ->label('Referral Code')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->badge(),

                                        TextEntry::make('type')
                                            ->badge()
                                            ->placeholder('—'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->placeholder('—'),
                                    ]),
                            ]),

                            Section::make('Biography')
                                ->description('User-provided profile info')
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
                                ->description('Demographics & onboarding')
                                ->columns([
                                    'default' => 1,
                                    'md' => 3,
                                ])
                                ->schema([
                                    TextEntry::make('gender')
                                        ->badge()
                                        ->placeholder('—'),

                                    TextEntry::make('dob')
                                        ->label('Date of Birth')
                                        ->date()
                                        ->placeholder('—'),

                                    IconEntry::make('onboarded')
                                        ->boolean()
                                        ->label('Onboarding Complete'),
                                ]),
                        ]),

                    Tab::make('Business Hub')
                        ->icon('heroicon-m-briefcase')
                        ->schema([
                            Section::make('Hierarchy & Growth')
                                ->description('Upline, level & activity timestamps')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('parent.name')
                                        ->label('Upline/Referrer')
                                        ->placeholder('Direct Registration'),

                                    TextEntry::make('level_id')
                                        ->label('Current Rank/Level')
                                        ->numeric()
                                        ->placeholder('—'),

                                    TextEntry::make('subscribed_at')
                                        ->label('Subscribed At')
                                        ->dateTime()
                                        ->placeholder('Not Subscribed'),

                                    TextEntry::make('created_at')
                                        ->label('Member Since')
                                        ->dateTime()
                                        ->placeholder('—'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
