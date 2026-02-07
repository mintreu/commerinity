<?php

namespace App\Filament\Resources\Ecommerce\Vouchers;

use App\Filament\Resources\Ecommerce\Vouchers\Pages\CreateVoucher;
use App\Filament\Resources\Ecommerce\Vouchers\Pages\EditVoucher;
use App\Filament\Resources\Ecommerce\Vouchers\Pages\ListVouchers;
use App\Filament\Resources\Ecommerce\Vouchers\Pages\ViewVoucher;
use App\Filament\Resources\Ecommerce\Vouchers\RelationManagers\VoucherCodesRelationManager;
use App\Filament\Resources\Ecommerce\Vouchers\Schemas\VoucherForm;
use App\Filament\Resources\Ecommerce\Vouchers\Schemas\VoucherInfolist;
use App\Filament\Resources\Ecommerce\Vouchers\Tables\VouchersTable;
use App\Models\Ecommerce\Voucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Ecommerce';

    public static function form(Schema $schema): Schema
    {
        return VoucherForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VoucherInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VouchersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VoucherCodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVouchers::route('/'),
            'create' => CreateVoucher::route('/create'),
            'view' => ViewVoucher::route('/{record}'),
            'edit' => EditVoucher::route('/{record}/edit'),
        ];
    }
}
