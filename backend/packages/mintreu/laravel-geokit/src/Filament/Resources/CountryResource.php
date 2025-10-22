<?php

namespace Mintreu\LaravelGeokit\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\ListCountries;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\CreateCountry;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\ViewCountry;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\EditCountry;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\Country;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Localization';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('iso_code_2')
                    ->required()
                    ->maxLength(255),
                TextInput::make('iso_code_3')
                    ->required()
                    ->maxLength(255),
                TextInput::make('isd_code')
                    ->required()
                    ->numeric(),
                TextInput::make('address_format')
                    ->maxLength(255),
                Toggle::make('postcode_required')
                    ->required(),
                TextInput::make('locale')
                    ->required()
                    ->maxLength(255)
                    ->default('en'),
                TextInput::make('region')
                    ->required()
                    ->maxLength(255),
                TextInput::make('timezone')
                    ->required()
                    ->maxLength(255),
                TextInput::make('timezone_diff')
                    ->required()
                    ->maxLength(255),
                TextInput::make('currency')
                    ->required()
                    ->maxLength(255)
                    ->default('USD'),
                TextInput::make('flag')
                    ->required()
                    ->maxLength(255),
                TextInput::make('exchange_rate'),
                TextInput::make('multiplier')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => 2
            ])
            ->columns([
               Stack::make([

                   ImageColumn::make('flag')
//                            ->width('200px')
//                            ->height('150px')
                       ->columnSpanFull()
                       ->alignCenter()
                       ->size('70%')
                       ->square(),

                   Grid::make(2)
                       ->schema([
                           Grid::make(3)
                               ->schema([
                                   TextColumn::make('name')
                                       ->size(TextSize::Large)
                                       ->prefix(__('Country:- '))
                                       ->grow()
                                       ->searchable(),
                                   IconColumn::make('is_active')
                                       ->boolean(),
                               ])
                               ->extraAttributes(['class' => 'mx-auto mt-3'])
                                ->columnSpanFull(),

                           TextColumn::make('iso_code_2')
                               ->badge()
                               ->description(__('ISO Code 2'))
                               ->alignCenter()
                               ->searchable(),
                           TextColumn::make('iso_code_3')
                               ->badge()
                               ->alignCenter()
                               ->description(__('ISO Code 3'))
                               ->searchable(),


                           Split::make([
                               TextColumn::make('created_at')
                                   ->dateTime()
                                   ->alignCenter()
                                   ->description('Create At')
                                   ->sortable()
                                   ->toggleable(isToggledHiddenByDefault: true),
                               TextColumn::make('updated_at')
                                   ->dateTime()
                                   ->alignCenter()
                                   ->description('Update At')
                                   ->sortable()
                                   ->toggleable(isToggledHiddenByDefault: true),
                           ])->columnSpanFull()

                       ])
                        ->columnSpanFull()


               ])->columnSpanFull()
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
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'view' => ViewCountry::route('/{record}'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
