<?php

namespace App\Filament\Resources\Ecommerce\Filters;

use App\Filament\Resources\Ecommerce\Filters\Pages\CreateFilter;
use App\Filament\Resources\Ecommerce\Filters\Pages\EditFilter;
use App\Filament\Resources\Ecommerce\Filters\Pages\ListFilters;
use App\Filament\Resources\Ecommerce\Filters\Pages\ViewFilter;
use App\Filament\Resources\Ecommerce\Filters\Schemas\FilterForm;
use App\Filament\Resources\Ecommerce\Filters\Schemas\FilterInfolist;
use App\Filament\Resources\Ecommerce\Filters\Tables\FiltersTable;
use App\Models\Ecommerce\Filter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FilterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FilterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FiltersTable::configure($table);
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
            'index' => ListFilters::route('/'),
            'create' => CreateFilter::route('/create'),
            'view' => ViewFilter::route('/{record}'),
            'edit' => EditFilter::route('/{record}/edit'),
        ];
    }
}
