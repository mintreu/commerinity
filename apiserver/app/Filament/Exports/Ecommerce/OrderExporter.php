<?php

namespace App\Filament\Exports\Ecommerce;

use App\Models\Ecommerce\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('order_number'),
            ExportColumn::make('customerable_type'),
            ExportColumn::make('customerable_id'),
            ExportColumn::make('status'),
            ExportColumn::make('subtotal'),
            ExportColumn::make('shipping_cost'),
            ExportColumn::make('tax'),
            ExportColumn::make('discount'),
            ExportColumn::make('total'),
            ExportColumn::make('total_bv'),
            ExportColumn::make('total_pv'),
            ExportColumn::make('total_reward_points'),
            ExportColumn::make('total_coins'),
            ExportColumn::make('commission_processed'),
            ExportColumn::make('shippingAddress.title'),
            ExportColumn::make('billingAddress.title'),
            ExportColumn::make('expire_at'),
            ExportColumn::make('delivered_at'),
            ExportColumn::make('return_period_ends_at'),
            ExportColumn::make('completed_at'),
            ExportColumn::make('voucher'),
            ExportColumn::make('tracking_id'),
            ExportColumn::make('payment_success'),
            ExportColumn::make('quantity'),
            ExportColumn::make('notes'),
            ExportColumn::make('admin_notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
