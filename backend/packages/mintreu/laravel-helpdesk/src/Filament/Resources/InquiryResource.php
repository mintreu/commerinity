<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages\ListInquiries;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages\CreateInquiry;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages\ViewInquiry;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages\EditInquiry;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\Inquiry;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'HelpDesk & Support';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact details')
                    ->description('Share basic contact information so we can follow up.')
                    ->aside()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Full name')
                                ->placeholder('Jane Doe')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Enter the primary contact person’s full name.')
                                ->prefixIcon('heroicon-m-user')
                                ->columnSpan(1),

                            TextInput::make('email')
                                ->label('Email address')
                                ->email()
                                ->placeholder('you@example.com')
                                ->required()
                                ->maxLength(255)
                                ->helperText('We’ll send confirmation and follow-ups here.')
                                ->prefixIcon('heroicon-m-envelope')
                                ->columnSpan(1),
                        ]),

                        Textarea::make('message')
                            ->label('Message')
                            ->placeholder('How can we help? Share as much detail as possible.')
                            ->rows(6)
                            ->required()
                            ->helperText('Include context, links, or references to speed things up.')
                            ->columnSpanFull(),

                        Toggle::make('is_business')
                            ->label('This is a business inquiry')
                            ->helperText('Enable to provide your company information.')
                            ->inline(false)
                            ->default(false),
                    ])
                    ->columns(1),

                Section::make('Business details')
                    ->description('Tell us a bit more about your company.')
                    ->collapsible()
                    ->aside()
                    ->schema([
                        Fieldset::make('Company information')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 2,
                                ])->schema([
                                    TextInput::make('company_name')
                                        ->label('Company name')
                                        ->placeholder('Acme Inc.')
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-m-building-office')
                                        ->required(fn (Get $get) => (bool) $get('is_business'))
                                        ->visible(fn (Get $get) => (bool) $get('is_business')),

                                    TextInput::make('phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->placeholder('+1 555 123 4567')
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-m-phone')
                                        ->visible(fn (Get $get) => (bool) $get('is_business')),
                                ]),

                                TextInput::make('address')
                                    ->label('Company address')
                                    ->placeholder('123 Street, City, Country')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->visible(fn (Get $get) => (bool) $get('is_business')),

                                TextInput::make('website')
                                    ->label('Website')
                                    ->placeholder('https://example.com')
                                    ->url()
                                    ->maxLength(255)
                                    ->suffixIcon('heroicon-m-globe-alt')
                                    ->helperText('Public company site, portfolio, or landing page.')
                                    ->visible(fn (Get $get) => (bool) $get('is_business')),
                            ])
                            ->columns(1),
                    ])
                    ->visible(fn (Get $get) => (bool) $get('is_business'))
                    ->compact(), // sleeker nested section look
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->latest())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                IconColumn::make('is_business')
                    ->label(__('Business Enquiry'))
                    ->boolean(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                //Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'create' => CreateInquiry::route('/create'),
            'view' => ViewInquiry::route('/{record}'),
            'edit' => EditInquiry::route('/{record}/edit'),
        ];
    }
}
