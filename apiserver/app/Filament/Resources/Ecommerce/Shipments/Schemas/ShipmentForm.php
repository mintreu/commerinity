<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Schemas;

use App\Casts\ShipmentStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                Select::make('pickup_address_id')
                    ->relationship('pickupAddress', 'title'),
                Select::make('delivery_address_id')
                    ->relationship('deliveryAddress', 'title'),
                TextInput::make('total_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(ShipmentStatusCast::class)
                    ->default('processing')
                    ->required(),
                TextInput::make('shipping_method'),
                TextInput::make('provider')
                    ->required()
                    ->default('native'),
                TextInput::make('shipping_provider_id')
                    ->numeric(),
                TextInput::make('provider_channel_id'),
                TextInput::make('provider_order_id'),
                TextInput::make('shipment_id'),
                TextInput::make('tracking_id'),
                TextInput::make('tracking_data'),
                TextInput::make('shipment_track_activities'),
                TextInput::make('last_update'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('cancelled_at'),
                DateTimePicker::make('return_initiated_at'),
                DateTimePicker::make('returned_at'),
                DateTimePicker::make('last_synced_at'),
                Toggle::make('cod')
                    ->required(),
                TextInput::make('cod_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cod_status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('cod_collected_at'),
                DateTimePicker::make('cod_remitted_at'),
                TextInput::make('charge')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
