<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('User Details')
                ->id('user-form-tabs')
                ->persistTab()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Account Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Grid::make([
                                'default' => 1,
                                'lg' => 3,
                            ])->schema([
                                // Left: Main account fields (2/3 width on desktop)
                                Group::make()
                                    ->columnSpan([
                                        'default' => 1,
                                        'lg' => 2,
                                    ])
                                    ->schema([
                                        Section::make('Primary Identification')
                                            ->description('Core account data and credentials')
                                            ->columns([
                                                'default' => 1,
                                                'md' => 2,
                                            ])
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Full name')
                                                    ->autofocus(),

                                                TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('name@example.com'),

                                                TextInput::make('mobile')
                                                    ->tel()
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('+91xxxxxxxxxx')
                                                    ->helperText('Include country code if applicable'),

                                                TextInput::make('referral_code')
                                                    ->disabled(fn (string $operation) => $operation === 'edit')
                                                    ->helperText('Generated on creation; cannot be changed later')
                                                    ->placeholder('AUTO-GENERATED'),

                                                TextInput::make('password')
                                                    ->password()
                                                    ->revealable()
                                                    ->label('Password')
                                                    ->required(fn (string $operation) => $operation === 'create')
                                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                                    ->dehydrated(fn ($state) => filled($state))
                                                    ->hidden(fn (string $operation) => $operation === 'view')
                                                    ->helperText(fn (string $operation) => $operation === 'edit'
                                                        ? 'Leave empty to keep existing password'
                                                        : null),
                                            ]),
                                    ]),

                                // Right: Media (1/3 width on desktop)
                                Group::make()
                                    ->columnSpan([
                                        'default' => 1,
                                        'lg' => 1,
                                    ])
                                    ->schema([
                                        Section::make('Avatar')
                                            ->description('Profile picture')
                                            ->collapsible()
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('avatar')
                                                    ->collection('avatar')
                                                    ->avatar()
                                                    ->image()
                                                    ->imageEditor()
                                                    ->helperText('Recommended: square image, 512×512+'),
                                            ]),
                                    ]),
                            ]),
                        ]),

                    Tab::make('Profile Details')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Personal Information')
                                ->description('Optional user profile data')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    Select::make('gender')
                                        ->options(GenderCast::class)
                                        ->default('other')
                                        ->required()
                                        ->native(false),

                                    DatePicker::make('dob')
                                        ->label('Date of Birth')
                                        ->native(false)
                                        ->closeOnDateSelection(),

                                    Textarea::make('bio')
                                        ->rows(4)
                                        ->columnSpanFull()
                                        ->placeholder('Short intro about the user…'),
                                ]),
                        ]),

                    Tab::make('Business & Hierarchy')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Section::make('Management & Relations')
                                ->description('User type, hierarchy, onboarding and verification')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    Select::make('type')
                                        ->options(UserTypeCast::class)
                                        ->default('regular')
                                        ->required()
                                        ->native(false),

                                    Select::make('status')
                                        ->options(UserStatusCast::class)
                                        ->default('draft')
                                        ->required()
                                        ->native(false),

                                    Select::make('parent_id')
                                        ->relationship('parent', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->label('Referrer/Parent')
                                        ->placeholder('Select a referrer')
                                        ->columnSpanFull(),

                                    Grid::make([
                                        'default' => 1,
                                        'md' => 2,
                                    ])->schema([
                                        TextInput::make('level_id')
                                            ->numeric()
                                            ->label('Affiliate Level')
                                            ->placeholder('e.g. 1'),

                                        Toggle::make('onboarded')
                                            ->label('Onboarding Complete')
                                            ->inline(false),
                                    ])->columnSpanFull(),

                                    DateTimePicker::make('email_verified_at')
                                        ->label('Email Verified At')
                                        ->seconds(false),

                                    DateTimePicker::make('mobile_verified_at')
                                        ->label('Mobile Verified At')
                                        ->seconds(false),

                                    DateTimePicker::make('subscribed_at')
                                        ->label('Subscription Active Since')
                                        ->seconds(false)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),
        ]);
    }
}
