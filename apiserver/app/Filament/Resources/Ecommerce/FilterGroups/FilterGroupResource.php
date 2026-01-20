<?php

namespace App\Filament\Resources\Ecommerce\FilterGroups;

use App\Filament\Resources\Ecommerce\FilterGroups\Pages\CreateFilterGroup;
use App\Filament\Resources\Ecommerce\FilterGroups\Pages\EditFilterGroup;
use App\Filament\Resources\Ecommerce\FilterGroups\Pages\ListFilterGroups;
use App\Filament\Resources\Ecommerce\FilterGroups\Pages\ViewFilterGroup;
use App\Filament\Resources\Ecommerce\FilterGroups\Schemas\FilterGroupForm;
use App\Filament\Resources\Ecommerce\FilterGroups\Schemas\FilterGroupInfolist;
use App\Filament\Resources\Ecommerce\FilterGroups\Tables\FilterGroupsTable;
use App\Models\Ecommerce\FilterGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FilterGroupResource extends Resource
{
    protected static ?string $model = FilterGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FilterGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FilterGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FilterGroupsTable::configure($table);
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
            'index' => ListFilterGroups::route('/'),
            'create' => CreateFilterGroup::route('/create'),
            'view' => ViewFilterGroup::route('/{record}'),
            'edit' => EditFilterGroup::route('/{record}/edit'),
        ];
    }
}
