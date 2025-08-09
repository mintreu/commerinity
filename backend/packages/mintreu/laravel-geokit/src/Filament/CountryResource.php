<?php

namespace Mintreu\LaravelGeokit\Filament;

use App\Filament\Resources\Localization\CountryResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\Country;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('iso_code_2')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('iso_code_3')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('isd_code')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('address_format')
                    ->maxLength(255),
                Forms\Components\Toggle::make('postcode_required')
                    ->required(),
                Forms\Components\TextInput::make('locale')
                    ->required()
                    ->maxLength(255)
                    ->default('en'),
                Forms\Components\TextInput::make('region')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('timezone')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('timezone_diff')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(255)
                    ->default('USD'),
                Forms\Components\TextInput::make('flag')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('exchange_rate'),
                Forms\Components\TextInput::make('multiplier')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso_code_3')
                    ->searchable(),
                Tables\Columns\TextColumn::make('isd_code')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_format')
                    ->searchable(),
                Tables\Columns\IconColumn::make('postcode_required')
                    ->boolean(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->searchable(),
                Tables\Columns\TextColumn::make('timezone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('timezone_diff')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flag')
                    ->searchable(),
                Tables\Columns\TextColumn::make('multiplier')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => \Mintreu\LaravelGeokit\Filament\CountryResource\Pages\ListCountries::route('/'),
            'create' => \Mintreu\LaravelGeokit\Filament\CountryResource\Pages\CreateCountry::route('/create'),
            'view' => \Mintreu\LaravelGeokit\Filament\CountryResource\Pages\ViewCountry::route('/{record}'),
            'edit' => \Mintreu\LaravelGeokit\Filament\CountryResource\Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
