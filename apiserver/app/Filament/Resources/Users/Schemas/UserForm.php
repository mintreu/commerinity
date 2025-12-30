<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Details')
                    ->tabs([
                        Tab::make('Account Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Primary Identification')
                                    ->description('Core account data and credentials')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->email()
                                                ->required()
                                                ->unique(ignoreRecord: true),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('mobile')
                                                ->tel()
                                                ->unique(ignoreRecord: true),
                                            TextInput::make('referral_code')
                                                ->disabled(fn ($operation) => $operation === 'edit')
                                                ->helperText('Unique referral code generated on creation'),
                                        ]),
                                        TextInput::make('password')
                                            ->password()
                                            ->revealable()
                                            ->required(fn ($operation) => $operation === 'create')
                                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->hidden(fn ($operation) => $operation === 'view'),
                                    ]),

                                Section::make('Media')
                                    ->collapsible()
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('avatar')
                                            ->collection('avatar')
                                            ->avatar()
                                            ->circular()
                                            ->imageEditor(),
                                    ]),
                            ]),

                        Tab::make('Profile Details')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Personal Information')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('gender')
                                                ->options(GenderCast::class)
                                                ->default('other')
                                                ->required(),
                                            DatePicker::make('dob')
                                                ->label('Date of Birth'),
                                        ]),
                                        Textarea::make('bio')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Business & Hierarchy')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Management & Relations')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('type')
                                                ->options(UserTypeCast::class)
                                                ->default('regular')
                                                ->required(),
                                            Select::make('status')
                                                ->options(UserStatusCast::class)
                                                ->default('draft')
                                                ->required(),
                                        ]),
                                        Grid::make(3)->schema([
                                            Select::make('parent_id')
                                                ->relationship('parent', 'name')
                                                ->searchable()
                                                ->label('Referrer/Parent'),
                                            TextInput::make('level_id')
                                                ->numeric()
                                                ->label('Affiliate Level'),
                                            Toggle::make('onboarded')
                                                ->label('Onboarding Complete')
                                                ->inline(false),
                                        ]),
                                        Grid::make(2)->schema([
                                            DateTimePicker::make('email_verified_at')
                                                ->label('Email Verified'),
                                            DateTimePicker::make('mobile_verified_at')
                                                ->label('Mobile Verified'),
                                        ]),
                                        DateTimePicker::make('subscribed_at')
                                            ->label('Subscription Active Since'),
                                    ]),
                            ]),
                    ])
                    ->persistTab()
                    ->id('user-form-tabs')
                    ->columnSpanFull(),
            ]);
    }
}
