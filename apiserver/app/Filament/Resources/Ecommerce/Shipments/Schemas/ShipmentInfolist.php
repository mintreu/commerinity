<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Schemas;


use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Shipment Overview')
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ])->schema([
                        TextEntry::make('order.id')
                            ->label('Order')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('total_quantity')
                            ->label('Total Quantity')
                            ->numeric()
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('provider')
                            ->label('Provider')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('shipping_method')
                            ->label('Shipping Method')
                            ->placeholder('-'),
                    ]),
                ])
                ->compact(),

            Section::make('Addresses')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('pickupAddress.title')
                            ->label('Pickup Address')
                            ->placeholder('-'),

                        TextEntry::make('deliveryAddress.title')
                            ->label('Delivery Address')
                            ->placeholder('-'),
                    ]),
                ])
                ->compact()
                ->collapsible(),

            Section::make('Provider References')
                ->icon('heroicon-o-identification')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ])->schema([
                        TextEntry::make('shipping_provider_id')
                            ->label('Shipping Provider ID')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('provider_channel_id')
                            ->label('Provider Channel ID')
                            ->placeholder('-'),

                        TextEntry::make('provider_order_id')
                            ->label('Provider Order ID')
                            ->placeholder('-'),

                        TextEntry::make('shipment_id')
                            ->label('Shipment ID')
                            ->placeholder('-'),

                        TextEntry::make('tracking_id')
                            ->label('Tracking ID')
                            ->placeholder('-'),
                    ]),
                ])
                ->compact()
                ->collapsible()
                ->collapsed(),

            Section::make('Timeline')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ])->schema([
                        TextEntry::make('shipped_at')
                            ->label('Shipped At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('delivered_at')
                            ->label('Delivered At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('cancelled_at')
                            ->label('Cancelled At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('return_initiated_at')
                            ->label('Return Initiated At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('returned_at')
                            ->label('Returned At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('last_synced_at')
                            ->label('Last Synced At')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                ])
                ->compact()
                ->collapsible()
                ->collapsed(),

            Section::make('COD')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ])->schema([
                        IconEntry::make('cod')
                            ->label('COD')
                            ->boolean(),

                        TextEntry::make('cod_status')
                            ->label('COD Status')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('cod_amount')
                            ->label('COD Amount')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('cod_collected_at')
                            ->label('Collected At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('cod_remitted_at')
                            ->label('Remitted At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('charge')
                            ->label('Charge')
                            ->numeric()
                            ->placeholder('-'),
                    ]),
                ])
                ->compact()
                ->collapsible(),

            Section::make('System')
                ->icon('heroicon-o-cog-6-tooth')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                ])
                ->compact()
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
