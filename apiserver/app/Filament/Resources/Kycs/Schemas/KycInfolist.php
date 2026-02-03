<?php

namespace App\Filament\Resources\Kycs\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class KycInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('KYC Details')
                ->id('kyc-infolist-tabs')
                ->persistTab()
                ->columnSpanFull()
                ->tabs([
                    /* -------------------------------------------------
                     | Overview
                     -------------------------------------------------*/
                    Tab::make('Overview')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('KYC Context')
                                ->description('Who this KYC belongs to')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('kycable_type')
                                        ->label('Owner Type'),

                                    TextEntry::make('kycable_id')
                                        ->label('Owner ID')
                                        ->numeric(),
                                ]),

                            Section::make('KYC Type')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('kyc_type')
                                        ->badge(),

                                    TextEntry::make('status')
                                        ->badge(),
                                ]),

                            Section::make('Business Details')
                                ->description('Only applicable for Business KYC')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('company_name')
                                        ->placeholder('—'),

                                    TextEntry::make('company_type')
                                        ->placeholder('—'),

                                    TextEntry::make('gst_number')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    /* -------------------------------------------------
                     | Documents
                     -------------------------------------------------*/
                    Tab::make('Documents')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('PAN')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('pan_number')
                                        ->label('PAN Number'),

                                    SpatieMediaLibraryImageEntry::make('panImage')
                                        ->label('PAN Image')
                                        ->collection('panImage')
                                        ->height(180),
                                ]),

                            Section::make('Aadhaar')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('aadhaar_number')
                                        ->label('Aadhaar Number')
                                        ->formatStateUsing(
                                            fn (?string $state) => $state
                                                ? preg_replace('/\d(?=\d{4})/', '•', $state)
                                                : '—'
                                        ),

                                    SpatieMediaLibraryImageEntry::make('aadhaarImage')
                                        ->label('Aadhaar Image')
                                        ->collection('aadhaarImage')
                                        ->height(180),
                                ]),
                        ]),

                    /* -------------------------------------------------
                     | Review & Audit
                     -------------------------------------------------*/
                    Tab::make('Review')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Section::make('Verification Timeline')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('submitted_at')
                                        ->label('Submitted At')
                                        ->dateTime()
                                        ->placeholder('—'),

                                    TextEntry::make('reviewed_at')
                                        ->label('Reviewed At')
                                        ->dateTime()
                                        ->placeholder('—'),
                                ]),

                            Section::make('Reviewer')
                                ->schema([
                                    TextEntry::make('reviewed_by')
                                        ->label('Reviewed By (User ID)')
                                        ->numeric()
                                        ->placeholder('—'),
                                ]),

                            Section::make('System Audit')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created At')
                                        ->dateTime(),

                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->dateTime(),
                                ]),
                        ]),
                ]),
        ]);
    }
}
