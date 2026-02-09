<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

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

                    self::getKycTab(),
                    self::getUserAddress(),
                    self::getUserBeneficiaryAccounts(),
                    Tab::make('Tab 3')
                        ->schema([
                            // ...
                        ]),


                ]),
        ]);
    }





    protected static function getKycTab(): Tab
    {
        return Tab::make('Identity')
            ->icon('heroicon-o-identification')
            ->schema([
                Section::make('KYC Details')
                    ->description('User identity verification information')
                    ->icon('heroicon-o-shield-check')
                    ->relationship('kyc')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('kyc_type')
                            ->label('KYC Type')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('pan_number')
                            ->label('PAN Number')
                            ->copyable()
                            ->icon('heroicon-o-credit-card'),

                        TextEntry::make('aadhaar_number')
                            ->label('Aadhaar Number')
                            ->copyable()
                            ->visible(fn ($record) => filled($record?->aadhaar_number)),

                        TextEntry::make('gst_number')
                            ->label('GST Number')
                            ->copyable()
                            ->icon('heroicon-o-receipt-tax')
                            ->visible(fn ($record) => filled($record?->gst_number)),

                        TextEntry::make('company_name')
                            ->label('Company Name')
                            ->visible(fn ($record) => filled($record?->company_name)),

                        TextEntry::make('company_type')
                            ->label('Company Type')
                            ->badge()
                            ->visible(fn ($record) => filled($record?->company_type)),

                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('reviewed_at')
                            ->label('Reviewed At')
                            ->dateTime()
                            ->icon('heroicon-o-check-circle'),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->color('danger')
                            ->columnSpanFull()
                            ->visible(fn ($record) => filled($record?->rejection_reason)),
                    ]),
            ]);
    }


    protected static function getUserAddress(): Tab
    {
        return Tab::make('All Addresses')
            ->icon('heroicon-o-map-pin')
            ->schema([
                RepeatableEntry::make('addresses')
                    ->label('')
                    ->grid()
                    ->schema([
                        Section::make(fn (Model $record) => $record->title ?? $record->uuid)
                            ->icon(fn (Model $record) => $record?->type?->getIcon() ?? 'heroicon-o-home-modern')
                            ->columns(2)
                            ->schema([

                                // 🔹 Address Type & Flags
                                TextEntry::make('type')
                                    ->label('Address Type')
                                    ->badge(),

                                IconEntry::make('default')
                                    ->label('Default')
                                    ->boolean(),

                                // 🔹 Contact Info
                                TextEntry::make('person_name')
                                    ->label('Contact Name')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('person_mobile')
                                    ->label('Mobile')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),

                                TextEntry::make('person_email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('alternate_contact')
                                    ->label('Alternate Contact')
                                    ->icon('heroicon-o-phone-arrow-up-right')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 Address Lines
                                TextEntry::make('address_1')
                                    ->label('Address Line 1')
                                    ->columnSpanFull(),

                                TextEntry::make('address_2')
                                    ->label('Address Line 2')
                                    ->columnSpanFull()
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('landmark')
                                    ->label('Landmark')
                                    ->icon('heroicon-o-map')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 Location Info
                                TextEntry::make('block.name')
                                    ->label('Block'),

                                TextEntry::make('city')
                                    ->label('City'),

                                TextEntry::make('state.name')
                                    ->label('State'),

                                TextEntry::make('country.name')
                                    ->label('Country'),

                                TextEntry::make('postal_code')
                                    ->label('Postal Code')
                                    ->icon('heroicon-o-hashtag'),

                                // 🔹 Coordinates (Optional)
                                TextEntry::make('latitude')
                                    ->label('Latitude')
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('longitude')
                                    ->label('Longitude')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 Meta
                                TextEntry::make('priority')
                                    ->label('Priority')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('pickup_location')
                                    ->label('Pickup Location')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
                            ]),
                    ]),
            ]);
    }



    protected static function getUserBeneficiaryAccounts(): Tab
    {
        return Tab::make('Beneficiary Accounts')
            ->icon('heroicon-o-banknotes')
            ->schema([
                RepeatableEntry::make('beneficiaryAccounts')
                    ->label('')
                    ->grid()
                    ->schema([
                        Section::make(fn (Model $record) => $record->holder_name ?? $record->uuid)
                            ->icon(fn (Model $record) => $record?->type?->getIcon() ?? 'heroicon-o-banknotes')
                            ->columns(2)
                            ->schema([

                                // 🔹 Type & Flags
                                TextEntry::make('type')
                                    ->label('Account Type')
                                    ->badge(),

                                IconEntry::make('is_default')
                                    ->label('Default')
                                    ->boolean(),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),

                                // 🔹 Holder Info
                                TextEntry::make('holder_name')
                                    ->label('Account Holder')
                                    ->icon('heroicon-o-user'),

                                // 🔹 Bank Details
                                TextEntry::make('bank_name')
                                    ->label('Bank Name')
                                    ->icon('heroicon-o-building-library')
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('branch_name')
                                    ->label('Branch')
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('account_number')
                                    ->label('Account Number')
                                    ->copyable()
                                    ->icon('heroicon-o-credit-card')
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('ifsc_code')
                                    ->label('IFSC Code')
                                    ->copyable()
                                    ->icon('heroicon-o-identification')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 UPI
                                TextEntry::make('upi_id')
                                    ->label('UPI ID')
                                    ->copyable()
                                    ->icon('heroicon-o-device-phone-mobile')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 Verification
                                TextEntry::make('verified_at')
                                    ->label('Verified At')
                                    ->dateTime()
                                    ->icon('heroicon-o-check-badge')
                                    ->visible(fn ($state) => filled($state)),

                                // 🔹 Provider / Meta
                                TextEntry::make('provider_beneficiary_id')
                                    ->label('Provider ID')
                                    ->visible(fn ($state) => filled($state)),

                                TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->color('danger')
                                    ->columnSpanFull()
                                    ->visible(fn ($state) => filled($state)),
                            ]),
                    ]),
            ]);
    }




}
