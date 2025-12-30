<?php

namespace App\Filament\Resources\Membership\Stages;

use App\Filament\Resources\Membership\Stages\Pages\CreateStage;
use App\Filament\Resources\Membership\Stages\Pages\EditStage;
use App\Filament\Resources\Membership\Stages\Pages\ListStages;
use App\Filament\Resources\Membership\Stages\Pages\ViewStage;
use App\Filament\Resources\Membership\Stages\Schemas\StageForm;
use App\Filament\Resources\Membership\Stages\Schemas\StageInfolist;
use App\Filament\Resources\Membership\Stages\Tables\StagesTable;
use App\Models\Membership\Stage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'LifeCycle';

    public static function form(Schema $schema): Schema
    {
        return StageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StagesTable::configure($table);
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
            'index' => ListStages::route('/'),
            'create' => CreateStage::route('/create'),
            'view' => ViewStage::route('/{record}'),
            'edit' => EditStage::route('/{record}/edit'),
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
