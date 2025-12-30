<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies;

use App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages\CreateAffiliateGenealogy;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages\EditAffiliateGenealogy;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages\ListAffiliateGenealogies;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages\ViewAffiliateGenealogy;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Schemas\AffiliateGenealogyForm;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Schemas\AffiliateGenealogyInfolist;
use App\Filament\Resources\Affiliate\AffiliateGenealogies\Tables\AffiliateGenealogiesTable;
use App\Models\Affiliate\AffiliateGenealogy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AffiliateGenealogyResource extends Resource
{
    protected static ?string $model = AffiliateGenealogy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Affiliate Mgmt';

    protected static ?string $pluralModelLabel = 'Affiliate Genealogy';

    public static function form(Schema $schema): Schema
    {
        return AffiliateGenealogyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AffiliateGenealogyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliateGenealogiesTable::configure($table);
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
            'index' => ListAffiliateGenealogies::route('/'),
            'create' => CreateAffiliateGenealogy::route('/create'),
            'view' => ViewAffiliateGenealogy::route('/{record}'),
            'edit' => EditAffiliateGenealogy::route('/{record}/edit'),
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
