<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\RelationManagers\OptionsRelationManager;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\ListFilters;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\CreateFilter;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\ViewFilter;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\EditFilter;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Models\Filter;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Catalogue';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('filterGroup')
                    ->relationship('groups','name')
                    ->required(),

                Toggle::make('is_required')->default(false)->required(),
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
            OptionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFilters::route('/'),
            'create' => CreateFilter::route('/create'),
            'view' => ViewFilter::route('/{record}'),
            'edit' => EditFilter::route('/{record}/edit'),
        ];
    }
}
