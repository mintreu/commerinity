<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\RelationManagers\FiltersRelationManager;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;

class FilterGroupResource extends Resource
{
    protected static ?string $model = FilterGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
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
            FiltersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages\ListFilterGroups::route('/'),
            'create' => \Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages\CreateFilterGroup::route('/create'),
            'view' => \Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages\ViewFilterGroup::route('/{record}'),
            'edit' => \Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages\EditFilterGroup::route('/{record}/edit'),
        ];
    }
}
