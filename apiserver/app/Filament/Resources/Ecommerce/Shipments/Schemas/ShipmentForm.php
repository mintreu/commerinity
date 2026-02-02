<?php

namespace App\Filament\Resources\Ecommerce\Shipments\Schemas;

use App\Casts\ShipmentStatusCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([

                // =========================
                // Core
                // =========================
                Section::make('Shipment')
                    ->description('Order, addresses, status and quantities')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            Select::make('pickup_address_id')
                                ->label('Pickup Address')
                                ->relationship('pickupAddress', 'title')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select pickup address')
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record) => $record->title ?: ('Address #' . $record->getKey())
                                ),

                            Select::make('delivery_address_id')
                                ->label('Delivery Address')
                                ->relationship('deliveryAddress', 'title')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select delivery address')
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record) => $record->title ?: ('Address #' . $record->getKey())
                                ),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('total_quantity')
                                ->label('Total Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            Select::make('status')
                                ->label('Status')
                                ->options(ShipmentStatusCast::class) // keep as you had
                                // If this ever errors: replace with ->options(ShipmentStatusCast::options())
                                ->default('processing')
                                ->required(),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('shipping_method')
                                ->label('Shipping Method')
                                ->placeholder('e.g. standard, express'),

                            TextInput::make('provider')
                                ->label('Provider')
                                ->required()
                                ->default('native')
                                ->placeholder('e.g. native, shiprocket, pathao'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),

                // =========================
                // Provider Identifiers
                // =========================
                Section::make('Provider IDs')
                    ->description('External references & identifiers')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('shipping_provider_id')
                                ->label('Shipping Provider ID')
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('Numeric id'),

                            TextInput::make('provider_channel_id')
                                ->label('Provider Channel ID')
                                ->placeholder('Channel reference'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('provider_order_id')
                                ->label('Provider Order ID')
                                ->placeholder('External order id'),

                            TextInput::make('shipment_id')
                                ->label('Shipment ID')
                                ->placeholder('External shipment id'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('tracking_id')
                                ->label('Tracking ID')
                                ->placeholder('Tracking number'),

                            TextInput::make('last_update')
                                ->label('Last Update')
                                ->placeholder('Free text / provider message'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),

                // =========================
                // Tracking Payloads
                // =========================
                Section::make('Tracking Data')
                    ->description('Provider payloads (raw)')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Textarea::make('tracking_data')
                            ->label('Tracking Data')
                            ->rows(3)
                            ->placeholder('JSON / raw response')
                            ->columnSpanFull(),

                        Textarea::make('shipment_track_activities')
                            ->label('Track Activities')
                            ->rows(3)
                            ->placeholder('JSON / activity list')
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),

                // =========================
                // Shipment Timeline
                // =========================
                Section::make('Timeline')
                    ->description('Shipment dates')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            DateTimePicker::make('shipped_at')->label('Shipped At'),
                            DateTimePicker::make('delivered_at')->label('Delivered At'),
                            DateTimePicker::make('cancelled_at')->label('Cancelled At'),
                            DateTimePicker::make('return_initiated_at')->label('Return Initiated At'),
                            DateTimePicker::make('returned_at')->label('Returned At'),
                            DateTimePicker::make('last_synced_at')->label('Last Synced At'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(),

                // =========================
                // COD
                // =========================
                Section::make('Cash on Delivery (COD)')
                    ->description('COD flags, amounts and settlement')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            Toggle::make('cod')
                                ->label('COD')
                                ->required()
                                ->onIcon('heroicon-o-check-circle')
                                ->offIcon('heroicon-o-x-circle'),

                            TextInput::make('cod_status')
                                ->label('COD Status')
                                ->required()
                                ->default('pending')
                                ->placeholder('pending / collected / remitted'),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            TextInput::make('cod_amount')
                                ->label('COD Amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('charge')
                                ->label('Charge')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])->schema([
                            DateTimePicker::make('cod_collected_at')->label('COD Collected At'),
                            DateTimePicker::make('cod_remitted_at')->label('COD Remitted At'),
                        ]),
                    ])
                    ->compact()
                    ->collapsible(),
            ])->columnSpanFull(),
        ]);
    }
}
