<?php

namespace Mintreu\LaravelGeokit\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\ListAddresses;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\CreateAddress;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\ViewAddress;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages\EditAddress;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\Pages;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\Address;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Localization';
    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->required()
                    ->maxLength(255),
                Toggle::make('default')
                    ->required(),
                TextInput::make('person_name')
                    ->maxLength(255),
                TextInput::make('person_email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('person_mobile')
                    ->maxLength(255),
                TextInput::make('alternate_contact')
                    ->maxLength(255),
                TextInput::make('landmark')
                    ->maxLength(255),
                TextInput::make('pickup_location')
                    ->maxLength(255),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('addressable_type')
                    ->maxLength(255),
                TextInput::make('addressable_id')
                    ->numeric(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Select::make('block_id')
                    ->relationship('block', 'name'),
                TextInput::make('state_code')
                    ->maxLength(255),
                TextInput::make('country_code')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('address_1')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                IconColumn::make('default')
                    ->boolean(),
                TextColumn::make('person_name')
                    ->searchable(),
                TextColumn::make('person_email')
                    ->searchable(),
                TextColumn::make('person_mobile')
                    ->searchable(),
                TextColumn::make('alternate_contact')
                    ->searchable(),
                TextColumn::make('landmark')
                    ->searchable(),
                TextColumn::make('pickup_location')
                    ->searchable(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('addressable_type')
                    ->searchable(),
                TextColumn::make('addressable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('block.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('state_code')
                    ->searchable(),
                TextColumn::make('country_code')
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
            'index' => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'view' => ViewAddress::route('/{record:uuid}'),
            'edit' => EditAddress::route('/{record:uuid}/edit'),
        ];
    }
}
