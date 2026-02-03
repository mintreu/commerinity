<?php

namespace App\Filament\Resources\Kycs\Schemas;

use App\Casts\KycStatusCast;
use Filament\Forms\Components\DateTimePicker;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class KycForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('KYC')
                ->persistTab()
                ->id('kyc-form-tabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('KYC Details')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('KYC Type & Owner')
                                ->description('Basic KYC metadata')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    // Usually these are set by system (polymorphic owner)
                                    TextInput::make('kycable_type')
                                        ->label('Owner Type')
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Set automatically by the system')
                                        ->hidden(fn (string $operation) => $operation === 'create'),

                                    TextInput::make('kycable_id')
                                        ->label('Owner ID')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Set automatically by the system')
                                        ->hidden(fn (string $operation) => $operation === 'create'),

                                    Select::make('kyc_type')
                                        ->label('KYC Type')
                                        ->options([
                                            'personal' => 'Personal',
                                            'business' => 'Business',
                                        ])
                                        ->default('personal')
                                        ->required()
                                        ->native(false),

                                    Group::make()
                                        ->columns([
                                            'default' => 1,
                                            'md' => 2,
                                        ])
                                        ->schema([
                                            TextInput::make('company_name')
                                                ->label('Company Name')
                                                ->placeholder('e.g. ABC Pvt Ltd')
                                                ->hidden(fn ($get) => $get('kyc_type') !== 'business'),

                                            TextInput::make('company_type')
                                                ->label('Company Type')
                                                ->placeholder('e.g. Pvt Ltd / LLP / Proprietorship')
                                                ->hidden(fn ($get) => $get('kyc_type') !== 'business'),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Documents')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('PAN')
                                ->description('PAN details and proof')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextInput::make('pan_number')
                                        ->label('PAN Number')
                                        ->required()
                                        ->placeholder('ABCDE1234F')
                                        ->maxLength(10)
                                        ->helperText('10-character PAN (e.g. ABCDE1234F)'),

                                    SpatieMediaLibraryFileUpload::make('panImage')
                                        ->label('PAN Image')
                                        ->collection('panImage')
                                        ->image()
                                        ->imageEditor()
                                        ->downloadable()
                                        ->openable()
                                        ->maxFiles(1)
                                        ->helperText('Upload clear PAN photo/scan'),
                                ]),

                            Section::make('Aadhaar')
                                ->description('Aadhaar details and proof')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextInput::make('aadhaar_number')
                                        ->label('Aadhaar Number')
                                        ->placeholder('XXXX XXXX XXXX')
                                        ->maxLength(14)
                                        ->helperText('Store only if your compliance allows it (masking recommended)'),

                                    SpatieMediaLibraryFileUpload::make('aadhaarImage')
                                        ->label('Aadhaar Image')
                                        ->collection('aadhaarImage')
                                        ->image()
                                        ->imageEditor()
                                        ->downloadable()
                                        ->openable()
                                        ->maxFiles(1)
                                        ->helperText('Upload Aadhaar photo/scan'),
                                ]),

                            Section::make('GST')
                                ->description('Business GST details (optional)')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextInput::make('gst_number')
                                        ->label('GST Number')
                                        ->placeholder('22AAAAA0000A1Z5')
                                        ->maxLength(15)
                                        ->hidden(fn ($get) => $get('kyc_type') !== 'business'),
                                ]),
                        ]),

                    Tab::make('Review')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Section::make('Status & Review')
                                ->description('Verification outcome and timestamps')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    Select::make('status')
                                        ->options(KycStatusCast::class)
                                        ->default('pending')
                                        ->required()
                                        ->native(false),

                                    TextInput::make('reviewed_by')
                                        ->label('Reviewed By (User ID)')
                                        ->numeric()
                                        ->disabled(fn (string $operation) => $operation !== 'create')
                                        ->helperText('Usually set automatically when admin reviews'),

                                    DateTimePicker::make('submitted_at')
                                        ->label('Submitted At')
                                        ->seconds(false),

                                    DateTimePicker::make('reviewed_at')
                                        ->label('Reviewed At')
                                        ->seconds(false),

                                    Textarea::make('rejection_reason')
                                        ->label('Rejection Reason')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->hidden(fn ($get) => $get('status') !== 'rejected')
                                        ->placeholder('Explain why this KYC was rejected...'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
