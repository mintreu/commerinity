<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ShipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.id')
                    ->label('Order'),
                TextEntry::make('pickupAddress.title')
                    ->label('Pickup address')
                    ->placeholder('-'),
                TextEntry::make('deliveryAddress.title')
                    ->label('Delivery address')
                    ->placeholder('-'),
                TextEntry::make('total_quantity')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('shipping_method')
                    ->placeholder('-'),
                TextEntry::make('provider'),
                TextEntry::make('shipping_provider_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('provider_channel_id')
                    ->placeholder('-'),
                TextEntry::make('provider_order_id')
                    ->placeholder('-'),
                TextEntry::make('shipment_id')
                    ->placeholder('-'),
                TextEntry::make('tracking_id')
                    ->placeholder('-'),
                TextEntry::make('shipped_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('delivered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cancelled_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('return_initiated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('returned_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('cod')
                    ->boolean(),
                TextEntry::make('cod_amount')
                    ->numeric(),
                TextEntry::make('cod_status'),
                TextEntry::make('cod_collected_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cod_remitted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('charge')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
