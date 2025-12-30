<?php

namespace App\Filament\Resources\Mlm\MlmCommissions;

use App\Filament\Resources\Mlm\MlmCommissions\Pages\CreateMlmCommission;
use App\Filament\Resources\Mlm\MlmCommissions\Pages\EditMlmCommission;
use App\Filament\Resources\Mlm\MlmCommissions\Pages\ListMlmCommissions;
use App\Filament\Resources\Mlm\MlmCommissions\Pages\ViewMlmCommission;
use App\Filament\Resources\Mlm\MlmCommissions\Schemas\MlmCommissionForm;
use App\Filament\Resources\Mlm\MlmCommissions\Schemas\MlmCommissionInfolist;
use App\Filament\Resources\Mlm\MlmCommissions\Tables\MlmCommissionsTable;
use App\Models\Mlm\MlmCommission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MlmCommissionResource extends Resource
{
    protected static ?string $model = MlmCommission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Affiliate Mgmt';

    protected static ?string $pluralModelLabel = 'Affiliate Commission';

    public static function form(Schema $schema): Schema
    {
        return MlmCommissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MlmCommissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MlmCommissionsTable::configure($table);
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
            'index' => ListMlmCommissions::route('/'),
            'create' => CreateMlmCommission::route('/create'),
            'view' => ViewMlmCommission::route('/{record}'),
            'edit' => EditMlmCommission::route('/{record}/edit'),
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
