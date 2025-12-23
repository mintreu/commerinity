<?php

namespace App\Filament\Resources\SmsProviders;

use App\Filament\Resources\SmsProviders\Pages\CreateSmsProvider;
use App\Filament\Resources\SmsProviders\Pages\EditSmsProvider;
use App\Filament\Resources\SmsProviders\Pages\ListSmsProviders;
use App\Filament\Resources\SmsProviders\Pages\ViewSmsProvider;
use App\Filament\Resources\SmsProviders\Schemas\SmsProviderForm;
use App\Filament\Resources\SmsProviders\Schemas\SmsProviderInfolist;
use App\Filament\Resources\SmsProviders\Tables\SmsProvidersTable;
use App\Models\Sms\SmsProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsProviderResource extends Resource
{
    protected static ?string $model = SmsProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SmsProviderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SmsProviderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsProvidersTable::configure($table);
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
            'index' => ListSmsProviders::route('/'),
            'create' => CreateSmsProvider::route('/create'),
            'view' => ViewSmsProvider::route('/{record}'),
            'edit' => EditSmsProvider::route('/{record}/edit'),
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
