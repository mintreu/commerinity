<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages\ListFilterGroups;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages\CreateFilterGroup;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages\ViewFilterGroup;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages\EditFilterGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\RelationManagers\FiltersRelationManager;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;

class FilterGroupResource extends Resource
{
    protected static ?string $model = FilterGroup::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Catalogue';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
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
            FiltersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFilterGroups::route('/'),
            'create' => CreateFilterGroup::route('/create'),
            'view' => ViewFilterGroup::route('/{record}'),
            'edit' => EditFilterGroup::route('/{record}/edit'),
        ];
    }
}
