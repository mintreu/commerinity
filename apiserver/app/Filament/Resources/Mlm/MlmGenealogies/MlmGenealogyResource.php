<?php

namespace App\Filament\Resources\Mlm\MlmGenealogies;

use App\Filament\Resources\Mlm\MlmGenealogies\Pages\CreateMlmGenealogy;
use App\Filament\Resources\Mlm\MlmGenealogies\Pages\EditMlmGenealogy;
use App\Filament\Resources\Mlm\MlmGenealogies\Pages\ListMlmGenealogies;
use App\Filament\Resources\Mlm\MlmGenealogies\Pages\ViewMlmGenealogy;
use App\Filament\Resources\Mlm\MlmGenealogies\Schemas\MlmGenealogyForm;
use App\Filament\Resources\Mlm\MlmGenealogies\Schemas\MlmGenealogyInfolist;
use App\Filament\Resources\Mlm\MlmGenealogies\Tables\MlmGenealogiesTable;
use App\Models\Mlm\MlmGenealogy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MlmGenealogyResource extends Resource
{
    protected static ?string $model = MlmGenealogy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Affiliate Mgmt';

    protected static ?string $pluralModelLabel = 'Affiliate Genealogy';

    public static function form(Schema $schema): Schema
    {
        return MlmGenealogyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MlmGenealogyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MlmGenealogiesTable::configure($table);
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
            'index' => ListMlmGenealogies::route('/'),
            'create' => CreateMlmGenealogy::route('/create'),
            'view' => ViewMlmGenealogy::route('/{record}'),
            'edit' => EditMlmGenealogy::route('/{record}/edit'),
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
