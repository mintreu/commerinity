<?php

namespace App\Filament\Resources\BeneficiaryAccounts;

use App\Filament\Resources\BeneficiaryAccounts\Pages\CreateBeneficiaryAccount;
use App\Filament\Resources\BeneficiaryAccounts\Pages\EditBeneficiaryAccount;
use App\Filament\Resources\BeneficiaryAccounts\Pages\ListBeneficiaryAccounts;
use App\Filament\Resources\BeneficiaryAccounts\Pages\ViewBeneficiaryAccount;
use App\Filament\Resources\BeneficiaryAccounts\Schemas\BeneficiaryAccountForm;
use App\Filament\Resources\BeneficiaryAccounts\Schemas\BeneficiaryAccountInfolist;
use App\Filament\Resources\BeneficiaryAccounts\Tables\BeneficiaryAccountsTable;
use App\Models\BeneficiaryAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BeneficiaryAccountResource extends Resource
{
    protected static ?string $model = BeneficiaryAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return BeneficiaryAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BeneficiaryAccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiaryAccountsTable::configure($table);
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
            'index' => ListBeneficiaryAccounts::route('/'),
            'create' => CreateBeneficiaryAccount::route('/create'),
            'view' => ViewBeneficiaryAccount::route('/{record}'),
            'edit' => EditBeneficiaryAccount::route('/{record}/edit'),
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
