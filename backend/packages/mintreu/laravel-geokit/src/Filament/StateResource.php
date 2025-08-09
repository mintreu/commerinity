<?php

namespace Mintreu\LaravelGeokit\Filament;

use App\Filament\Resources\Localization\StateResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\State;

class StateResource extends Resource
{
    protected static ?string $model = State::class;
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            \Mintreu\LaravelGeokit\Filament\StateResource\Pages\ViewState::class,
            \Mintreu\LaravelGeokit\Filament\StateResource\Pages\EditState::class,
            \Mintreu\LaravelGeokit\Filament\StateResource\Pages\ManageBlocks::class
        ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blocks_count')->counts('blocks')->badge(),
                Tables\Columns\TextColumn::make('country.name')
                    ->numeric()
                    ->sortable(),
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
            'index' => \Mintreu\LaravelGeokit\Filament\StateResource\Pages\ListStates::route('/'),
            'create' => \Mintreu\LaravelGeokit\Filament\StateResource\Pages\CreateState::route('/create'),
            'view' => \Mintreu\LaravelGeokit\Filament\StateResource\Pages\ViewState::route('/{record}'),
            'edit' => \Mintreu\LaravelGeokit\Filament\StateResource\Pages\EditState::route('/{record}/edit'),
            'blocks' => \Mintreu\LaravelGeokit\Filament\StateResource\Pages\ManageBlocks::route('/{record}/blocks'),
        ];
    }
}
