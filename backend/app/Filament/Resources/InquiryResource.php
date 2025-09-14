<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Filament\Resources\InquiryResource\RelationManagers;
use App\Models\Inquiry;
use Filament\Forms;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact details')
                    ->description('Share basic contact information so we can follow up.')
                    ->aside()
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Full name')
                                ->placeholder('Jane Doe')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Enter the primary contact person’s full name.')
                                ->prefixIcon('heroicon-m-user')
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('email')
                                ->label('Email address')
                                ->email()
                                ->placeholder('you@example.com')
                                ->required()
                                ->maxLength(255)
                                ->helperText('We’ll send confirmation and follow-ups here.')
                                ->prefixIcon('heroicon-m-envelope')
                                ->columnSpan(1),
                        ]),

                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->placeholder('How can we help? Share as much detail as possible.')
                            ->rows(6)
                            ->required()
                            ->helperText('Include context, links, or references to speed things up.')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_business')
                            ->label('This is a business inquiry')
                            ->helperText('Enable to provide your company information.')
                            ->inline(false)
                            ->default(false),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Business details')
                    ->description('Tell us a bit more about your company.')
                    ->collapsible()
                    ->aside()
                    ->schema([
                        Forms\Components\Fieldset::make('Company information')
                            ->schema([
                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 2,
                                ])->schema([
                                    Forms\Components\TextInput::make('company_name')
                                        ->label('Company name')
                                        ->placeholder('Acme Inc.')
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-m-building-office')
                                        ->required(fn (Forms\Get $get) => (bool) $get('is_business'))
                                        ->visible(fn (Forms\Get $get) => (bool) $get('is_business')),

                                    Forms\Components\TextInput::make('phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->placeholder('+1 555 123 4567')
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-m-phone')
                                        ->visible(fn (Forms\Get $get) => (bool) $get('is_business')),
                                ]),

                                Forms\Components\TextInput::make('address')
                                    ->label('Company address')
                                    ->placeholder('123 Street, City, Country')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->visible(fn (Forms\Get $get) => (bool) $get('is_business')),

                                Forms\Components\TextInput::make('website')
                                    ->label('Website')
                                    ->placeholder('https://example.com')
                                    ->url()
                                    ->maxLength(255)
                                    ->suffixIcon('heroicon-m-globe-alt')
                                    ->helperText('Public company site, portfolio, or landing page.')
                                    ->visible(fn (Forms\Get $get) => (bool) $get('is_business')),
                            ])
                            ->columns(1),
                    ])
                    ->visible(fn (Forms\Get $get) => (bool) $get('is_business'))
                    ->compact(), // sleeker nested section look
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->latest())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_business')
                    ->label(__('Business Enquiry'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListInquiries::route('/'),
            'create' => Pages\CreateInquiry::route('/create'),
            'view' => Pages\ViewInquiry::route('/{record}'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
