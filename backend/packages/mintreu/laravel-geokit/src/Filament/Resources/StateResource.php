<?php

namespace Mintreu\LaravelGeokit\Filament\Resources;

use Filament\Pages\Enums\SubNavigationPosition;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages\ViewState;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages\EditState;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages\ManageBlocks;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages\ListStates;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages\CreateState;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelGeokit\Models\State;

class StateResource extends Resource
{
    protected static ?string $model = State::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Localization';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $recordRouteKeyName = 'code';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewState::class,
            EditState::class,
            ManageBlocks::class
        ]);
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('blocks_count')->counts('blocks')->badge(),
                TextColumn::make('country.name')
                    ->numeric()
                    ->sortable(),
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
            'index' => ListStates::route('/'),
            'create' => CreateState::route('/create'),
            'view' => ViewState::route('/{record:code}'),
            'edit' => EditState::route('/{record}:code/edit'),
            'blocks' => ManageBlocks::route('/{record:code}/blocks'),
        ];
    }
}
