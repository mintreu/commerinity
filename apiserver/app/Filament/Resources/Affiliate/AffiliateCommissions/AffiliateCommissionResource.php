<?php

namespace App\Filament\Resources\Affiliate\AffiliateCommissions;

use App\Filament\Resources\Affiliate\AffiliateCommissions\Pages\CreateAffiliateCommission;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Pages\EditAffiliateCommission;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Pages\ListAffiliateCommissions;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Pages\ViewAffiliateCommission;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Schemas\AffiliateCommissionForm;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Schemas\AffiliateCommissionInfolist;
use App\Filament\Resources\Affiliate\AffiliateCommissions\Tables\AffiliateCommissionsTable;
use App\Models\Affiliate\AffiliateCommission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AffiliateCommissionResource extends Resource
{
    protected static ?string $model = AffiliateCommission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Affiliate Mgmt';

    protected static ?string $pluralModelLabel = 'Affiliate Commission';

    public static function form(Schema $schema): Schema
    {
        return AffiliateCommissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AffiliateCommissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliateCommissionsTable::configure($table);
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
            'index' => ListAffiliateCommissions::route('/'),
            'create' => CreateAffiliateCommission::route('/create'),
            'view' => ViewAffiliateCommission::route('/{record}'),
            'edit' => EditAffiliateCommission::route('/{record}/edit'),
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
