<?php

namespace Mintreu\LaravelIntegration\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages\ListIntegrations;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages\CreateIntegration;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages\ViewIntegration;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages\EditIntegration;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ---------------- Integration Details ----------------
                Section::make('Integration Details')
                    ->description('Basic information about the integration such as name and endpoint URL.')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Integration Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. PayPal, Stripe, Zoom')
                            ->hint('Enter a clear, descriptive name.'),

                        TextInput::make('url')
                            ->label('Integration URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('https://example.com/api')
                            ->hint('Base endpoint or official URL for the integration.'),
                    ]),

                // ---------------- Provider ----------------
                Section::make('Provider')
                    ->description('Details about the provider offering this integration.')
                    ->aside()
                    ->schema([
                        ToggleButtons::make('branding_method')
                            ->label('Branding Method')
                            ->live()
                            ->default(true)
                            ->inline()
                            ->inlineLabel()
                            ->options([
                                true => 'Paste Direct logo url',
                                false => 'Upload logo image'
                            ]),
                        TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->placeholder('Paste the provider logo URL')
                            ->maxLength(255)
                            ->visible(fn(Get $get) => $get('branding_method'))
                            ->helperText('Use this if you already have the image hosted.'),

                        FileUpload::make('logo_url')
                            ->label('Upload Logo')
                            ->image()
                            ->imageEditor()
                            ->hint('Or upload a logo image instead.')
                            ->helperText('Recommended size: 200x200px, PNG or JPG.')
                            ->visible(fn(Get $get) => !$get('branding_method'))
                            ->multiple(false),

                        TextInput::make('link')
                            ->label('Provider Link')
                            ->placeholder('https://provider.com')
                            ->maxLength(255)
                            ->hint('Optional: Add the provider’s official website.'),

                        TextInput::make('charge')
                            ->label('Charge Amount')
                            ->required()
                            ->numeric()
                            ->default(0.00)
                            ->placeholder('e.g. 29.99')
                            ->helperText('Specify default charge (0.00 if free).'),
                    ]),

                // ---------------- Description ----------------
                Section::make('Description')
                    ->description('Write a short summary about the integration.')
                    ->aside()
                    ->schema([
                        Textarea::make('desc')
                            ->hiddenLabel()
                            ->placeholder('Write a brief description...')
                            ->maxLength(1024)
                            ->helperText('Keep it concise. Max 1024 characters.'),
                    ]),

                // ---------------- Visibility ----------------
                Section::make('Visibility')
                    ->description('Control how this integration is displayed to users.')
                    ->aside()
                    ->schema([
                        Select::make('type')
                            ->label('Integration Type')
                            ->options(collect(IntegrationTypeCast::cases())
                                ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                            ->required()
                            ->hint('Choose the type that best matches this integration.'),

                        Flex::make([
                            Toggle::make('status')
                                ->label('Active')
                                ->required()
                                ->helperText('Enable or disable this integration.'),

                            Toggle::make('default')
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
                ImageColumn::make('logo_url')
                    ->searchable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('status')
                    ->boolean(),
                ToggleColumn::make('default')
                    ->default(false),
                ToggleColumn::make('is_live')
                    ->label('Alive')
                    ->default(false),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
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
                EditAction::make(),
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
            'index' => ListIntegrations::route('/'),
            'create' => CreateIntegration::route('/create'),
            'view' => ViewIntegration::route('/{record}'),
            'edit' => EditIntegration::route('/{record}/edit'),
        ];
    }
}
