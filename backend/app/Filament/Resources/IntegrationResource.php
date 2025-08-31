<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Filament\Resources\IntegrationResource\RelationManagers;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use \Mintreu\LaravelIntegration\Models\Integration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ---------------- Integration Details ----------------
                Forms\Components\Section::make('Integration Details')
                    ->description('Basic information about the integration such as name and endpoint URL.')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Integration Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. PayPal, Stripe, Zoom')
                            ->hint('Enter a clear, descriptive name.'),

                        Forms\Components\TextInput::make('url')
                            ->label('Integration URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('https://example.com/api')
                            ->hint('Base endpoint or official URL for the integration.'),
                    ]),

                // ---------------- Provider ----------------
                Forms\Components\Section::make('Provider')
                    ->description('Details about the provider offering this integration.')
                    ->aside()
                    ->schema([
                        Forms\Components\ToggleButtons::make('branding_method')
                            ->label('Branding Method')
                            ->live()
                            ->default(true)
                            ->inline()
                            ->inlineLabel()
                            ->options([
                                true => 'Paste Direct logo url',
                                false => 'Upload logo image'
                            ]),
                        Forms\Components\TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->placeholder('Paste the provider logo URL')
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('branding_method'))
                            ->helperText('Use this if you already have the image hosted.'),

                        Forms\Components\FileUpload::make('logo_url')
                            ->label('Upload Logo')
                            ->image()
                            ->imageEditor()
                            ->hint('Or upload a logo image instead.')
                            ->helperText('Recommended size: 200x200px, PNG or JPG.')
                            ->visible(fn(Forms\Get $get) => !$get('branding_method'))
                            ->multiple(false),

                        Forms\Components\TextInput::make('link')
                            ->label('Provider Link')
                            ->placeholder('https://provider.com')
                            ->maxLength(255)
                            ->hint('Optional: Add the provider’s official website.'),

                        Forms\Components\TextInput::make('charge')
                            ->label('Charge Amount')
                            ->required()
                            ->numeric()
                            ->default(0.00)
                            ->placeholder('e.g. 29.99')
                            ->helperText('Specify default charge (0.00 if free).'),
                    ]),

                // ---------------- Description ----------------
                Forms\Components\Section::make('Description')
                    ->description('Write a short summary about the integration.')
                    ->aside()
                    ->schema([
                        Forms\Components\Textarea::make('desc')
                            ->hiddenLabel()
                            ->placeholder('Write a brief description...')
                            ->maxLength(1024)
                            ->helperText('Keep it concise. Max 1024 characters.'),
                    ]),

                // ---------------- Visibility ----------------
                Forms\Components\Section::make('Visibility')
                    ->description('Control how this integration is displayed to users.')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Integration Type')
                            ->options(collect(IntegrationTypeCast::cases())
                                ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                            ->required()
                            ->hint('Choose the type that best matches this integration.'),

                        Forms\Components\Split::make([
                            Forms\Components\Toggle::make('status')
                                ->label('Active')
                                ->required()
                                ->helperText('Enable or disable this integration.'),

                            Forms\Components\Toggle::make('default')
                                ->label('Default')
                                ->required()
                                ->helperText('Mark as the default integration of its type.'),
                        ]),
                    ]),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('type')
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('default')
                    ->default(false),
                Tables\Columns\ToggleColumn::make('is_live')
                    ->label('Alive')
                    ->default(false),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListIntegrations::route('/'),
            'create' => Pages\CreateIntegration::route('/create'),
            'view' => Pages\ViewIntegration::route('/{record}'),
            'edit' => Pages\EditIntegration::route('/{record}/edit'),
        ];
    }
}
