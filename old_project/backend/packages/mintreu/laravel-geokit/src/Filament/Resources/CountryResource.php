<?php

namespace Mintreu\LaravelGeokit\Filament\Resources;

use Mintreu\LaravelGeokit\Filament\Resources\CountryResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\Country;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationGroup = 'Localization';

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
            ->contentGrid([
                'default' => 1,
                'md' => 2
            ])
            ->columns([
               Tables\Columns\Layout\Stack::make([

                   Tables\Columns\ImageColumn::make('flag')
//                            ->width('200px')
//                            ->height('150px')
                       ->columnSpanFull()
                       ->alignCenter()
                       ->size('70%')
                       ->square(),

                   Tables\Columns\Layout\Grid::make(2)
                       ->schema([
                           Tables\Columns\Layout\Grid::make(3)
                               ->schema([
                                   Tables\Columns\TextColumn::make('name')
                                       ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                                       ->prefix(__('Country:- '))
                                       ->grow()
                                       ->searchable(),
                                   Tables\Columns\IconColumn::make('is_active')
                                       ->boolean(),
                               ])
                               ->extraAttributes(['class' => 'mx-auto mt-3'])
                                ->columnSpanFull(),

                           Tables\Columns\TextColumn::make('iso_code_2')
                               ->badge()
                               ->description(__('ISO Code 2'))
                               ->alignCenter()
                               ->searchable(),
                           Tables\Columns\TextColumn::make('iso_code_3')
                               ->badge()
                               ->alignCenter()
                               ->description(__('ISO Code 3'))
                               ->searchable(),


                           Tables\Columns\Layout\Split::make([
                               Tables\Columns\TextColumn::make('created_at')
                                   ->dateTime()
                                   ->alignCenter()
                                   ->description('Create At')
                                   ->sortable()
                                   ->toggleable(isToggledHiddenByDefault: true),
                               Tables\Columns\TextColumn::make('updated_at')
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
            'index' => \Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\ListCountries::route('/'),
            'create' => \Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\CreateCountry::route('/create'),
            'view' => \Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\ViewCountry::route('/{record}'),
            'edit' => \Mintreu\LaravelGeokit\Filament\Resources\CountryResource\Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
