<?php

namespace App\Filament\Resources\UserResource\Schema;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Mintreu\Toolkit\Casts\GenderCast;

trait HasUserFormSchema
{


    public function getUserCreationFormSchema():array
    {
        return [

            $this->getUserFormSchema()
        ];
    }


    protected function getUserFormSchema(): Section
    {
        return Section::make('Member Information')
            ->description('Provide accurate and up-to-date member details. Fields marked with * are required.')
            ->aside()
            ->schema([

                TextInput::make('name')
                    ->label('Full Name')
                    ->placeholder('Enter full name')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Please use the full legal name as it appears on official documents.')
                    ->hint('This name will appear on all records.')
                    ->validationAttribute('name'),

                TextInput::make('mobile')
                    ->label('Mobile Number')
                    ->placeholder('e.g., +1 555 123 4567')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Enter a valid mobile number with country code.')
                    ->hint('Used for account verification and notifications.')
                    ->validationMessages([
                        'required' => 'A mobile number is required.',
                        'unique' => 'This mobile number is already registered.',
                    ]),

                TextInput::make('email')
                    ->label('Email Address')
                    ->placeholder('user@example.com')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('We will send important updates to this email.')
                    ->hint('Ensure the email is valid and accessible.')
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Email is mandatory.',
                        'email' => 'Please provide a valid email address.',
                        'unique' => 'This email is already in use.',
                    ]),

                Select::make('parent_id')
                    ->label('Upline Member (Optional)')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Select the immediate upline or supervising member under whom this user is placed.')
                    ->hint('Leave blank if this member is at the top level or has no upline.')
                    ->placeholder('Select upline member'),


                DatePicker::make('dob')
                    ->label('Date of Birth')
                    ->placeholder('Select date of birth')
                    ->required()
                    ->maxDate(now()->subYears(13))
                    ->helperText('Members must be at least 13 years old.')
                    ->hint('Used for age verification and profile completeness.')
                    ->displayFormat('F j, Y'),

                Select::make('gender')
                    ->label('Gender')
                    ->options(collect(GenderCast::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                    )
                    ->required()
                    ->helperText('Select the gender that best describes the member.')
                    ->hint('This information is used for personalization purposes.')
                    ->native(false),
            ]);
    }






    protected function getUserUpdateFormSchema(): array
    {
        return [

            Section::make('Member Profile')
                ->description('Personal and hierarchical details of the member.')
                ->schema([
                    Fieldset::make('Personal Information')
                        ->schema([

                            Fieldset::make('General Credentials')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Full Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull()
                                        ->placeholder('Enter full name')
                                        ->helperText('Use the member’s full legal name.')
                                        ->hint('Displayed in user profiles and records.'),


                            Select::make('gender')
                                ->label('Gender')
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other',
                                ])
                                ->required()
                                ->default('other')
                                ->helperText('Select the gender that best applies.')
                                ->native(false),

                            DatePicker::make('dob')
                                ->label('Date of Birth')
                                ->placeholder('Select date of birth')
                                ->maxDate(now()->subYears(13))
                                ->helperText('Member must be at least 13 years old.'),

                            Textarea::make('bio')
                                ->label('Bio / Notes')
                                ->placeholder('Write a short description or internal notes about this member...')
                                ->columnSpanFull()
                                ->maxLength(1000)
                                ->helperText('Visible only to administrators.'),
                        ]),

//            Section::make('Account Information')
//                ->description('Define the core account credentials and verification details for the member.')
//                ->schema([
//
//                    TextInput::make('password')
//                        ->label('Password')
//                        ->password()
//                        ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
//                        ->revealable()
//                        ->maxLength(255)
//                        ->helperText('Minimum 8 characters recommended.')
//                        ->hint('Keep it secure — you can reset it later if needed.'),
//
//
//
//                        ]),

                    Fieldset::make('Contact Details')
                        ->schema([
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->placeholder('user@example.com')
                                ->helperText('Used for login and communication.')
                                ->hint('Ensure a valid email address is provided.'),

                            DateTimePicker::make('email_verified_at')
                                ->label('Email Verified At')
                                ->helperText('Timestamp when the user’s email was verified.')
                                ->hint('Leave blank if not verified yet.'),

                            TextInput::make('mobile')
                                ->label('Mobile Number')
                                ->maxLength(255)
                                ->placeholder('e.g., +1 555 123 4567')
                                ->helperText('Used for OTP and contact purposes.'),

                            DateTimePicker::make('mobile_verified_at')
                                ->label('Mobile Verified At')
                                ->helperText('Timestamp when mobile number was verified.')
                                ->hint('Leave blank if unverified.'),
                        ]),
                ]),



                    Fieldset::make('Upline and Origin Information')
                        ->schema([
                            Select::make('parent_id')
                                ->label('Upline Member (Optional)')
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder('Select upline member')
                                ->helperText('Assign the immediate upline member in the hierarchy.')
                                ->hint('Leave blank if this user is top-level.'),

                            TextInput::make('referral_code')
                                ->label('Referral Code')
                                ->maxLength(255)
                                ->helperText('Auto-generated or manually assigned referral code for tracking.'),

                            TextInput::make('originator_type')
                                ->label('Originator Type')
                                ->maxLength(255)
                                ->placeholder('e.g., Admin, API, Import')
                                ->helperText('Source of this record creation.'),

                            TextInput::make('originator_id')
                                ->label('Originator ID')
                                ->numeric()
                                ->placeholder('Enter ID of originator')
                                ->helperText('Used for traceability in the system.'),
                        ]),
                ]),

            Section::make('Status and Classification')
                ->description('Define the type, current status, and administrative feedback for this member.')
                ->schema([
                    Fieldset::make('Account Type & Status')
                        ->schema([

                            Select::make('type')
                                ->label('Member Type')
                                ->options(collect(AuthTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])->toArray())
                                ->disabled()
                                ->required()
                                ->helperText('Specifies the membership or user category.'),



                            Select::make('status')
                                ->label('Account Status')
                                ->options(collect(AuthStatusCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])->toArray())
                                ->required()
                                ->disabled()
                                ->helperText('Defines current account lifecycle state.'),


                        ]),
                ]),
        ];
    }



}
