<?php

namespace App\Filament\Exports\Ecommerce;

use App\Models\Ecommerce\Shipment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ShipmentExporter extends Exporter
{
    protected static ?string $model = Shipment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('order.id'),
            ExportColumn::make('pickupAddress.title'),
            ExportColumn::make('deliveryAddress.title'),
            ExportColumn::make('total_quantity'),
            ExportColumn::make('status'),
            ExportColumn::make('shipping_method'),
            ExportColumn::make('provider'),
            ExportColumn::make('shipping_provider_id'),
            ExportColumn::make('provider_channel_id'),
            ExportColumn::make('provider_order_id'),
            ExportColumn::make('shipment_id'),
            ExportColumn::make('tracking_id'),
            ExportColumn::make('tracking_data'),
            ExportColumn::make('shipment_track_activities'),
            ExportColumn::make('last_update'),
            ExportColumn::make('shipped_at'),
            ExportColumn::make('delivered_at'),
            ExportColumn::make('cancelled_at'),
            ExportColumn::make('return_initiated_at'),
            ExportColumn::make('returned_at'),
            ExportColumn::make('last_synced_at'),
            ExportColumn::make('cod'),
            ExportColumn::make('cod_amount'),
            ExportColumn::make('cod_status'),
            ExportColumn::make('cod_collected_at'),
            ExportColumn::make('cod_remitted_at'),
            ExportColumn::make('charge'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your shipment export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
