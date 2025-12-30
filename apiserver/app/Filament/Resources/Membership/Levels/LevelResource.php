<?php

namespace App\Filament\Resources\Membership\Levels;

use App\Filament\Resources\Membership\Levels\Pages\CreateLevel;
use App\Filament\Resources\Membership\Levels\Pages\EditLevel;
use App\Filament\Resources\Membership\Levels\Pages\ListLevels;
use App\Filament\Resources\Membership\Levels\Pages\ViewLevel;
use App\Filament\Resources\Membership\Levels\Schemas\LevelForm;
use App\Filament\Resources\Membership\Levels\Schemas\LevelInfolist;
use App\Filament\Resources\Membership\Levels\Tables\LevelsTable;
use App\Models\Membership\Level;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'LifeCycle';

    public static function form(Schema $schema): Schema
    {
        return LevelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LevelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LevelsTable::configure($table);
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
            'index' => ListLevels::route('/'),
            'create' => CreateLevel::route('/create'),
            'view' => ViewLevel::route('/{record}'),
            'edit' => EditLevel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
