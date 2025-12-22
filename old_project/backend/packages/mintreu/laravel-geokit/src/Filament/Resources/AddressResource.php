<?php

namespace Mintreu\LaravelGeokit\Filament\Resources;

use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\Address;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Localization';
    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_1')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('postal_code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('default')
                    ->required(),
                Forms\Components\TextInput::make('person_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('person_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('person_mobile')
                    ->maxLength(255),
                Forms\Components\TextInput::make('alternate_contact')
                    ->maxLength(255),
                Forms\Components\TextInput::make('landmark')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pickup_location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('addressable_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('addressable_id')
                    ->numeric(),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\Select::make('block_id')
                    ->relationship('block', 'name'),
                Forms\Components\TextInput::make('state_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('country_code')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable(),
                Tables\Columns\IconColumn::make('default')
                    ->boolean(),
                Tables\Columns\TextColumn::make('person_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_mobile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alternate_contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('landmark')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('addressable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('addressable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('block.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_code')
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
            'index' => \Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\ListAddresses::route('/'),
            'create' => \Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\CreateAddress::route('/create'),
            'view' => \Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\ViewAddress::route('/{record:uuid}'),
            'edit' => \Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\EditAddress::route('/{record:uuid}/edit'),
        ];
    }
}
