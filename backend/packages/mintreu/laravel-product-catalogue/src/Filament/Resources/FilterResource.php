<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources;

use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelProductCatalogue\Models\Filter;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('filterGroup')
                    ->relationship('groups','name')
                    ->required(),

                Forms\Components\Toggle::make('is_required')->default(false)->required(),
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
            \Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\RelationManagers\OptionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\ListFilters::route('/'),
            'create' => \Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\CreateFilter::route('/create'),
            'view' => \Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\ViewFilter::route('/{record}'),
            'edit' => \Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages\EditFilter::route('/{record}/edit'),
        ];
    }
}
