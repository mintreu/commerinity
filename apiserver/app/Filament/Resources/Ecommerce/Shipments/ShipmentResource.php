<?php

namespace App\Filament\Resources\Ecommerce\Shipments;

use App\Filament\Resources\Ecommerce\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Ecommerce\Shipments\Pages\EditShipment;
use App\Filament\Resources\Ecommerce\Shipments\Pages\ListShipments;
use App\Filament\Resources\Ecommerce\Shipments\Pages\ViewShipment;
use App\Filament\Resources\Ecommerce\Shipments\Schemas\ShipmentForm;
use App\Filament\Resources\Ecommerce\Shipments\Schemas\ShipmentInfolist;
use App\Filament\Resources\Ecommerce\Shipments\Tables\ShipmentsTable;
use App\Models\Ecommerce\Shipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|null|\UnitEnum $navigationGroup = 'Ecommerce';

    public static function form(Schema $schema): Schema
    {
        return ShipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShipmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShipmentsTable::configure($table);
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
            'index' => ListShipments::route('/'),
            'create' => CreateShipment::route('/create'),
            'view' => ViewShipment::route('/{record}'),
            'edit' => EditShipment::route('/{record}/edit'),
        ];
    }
}
