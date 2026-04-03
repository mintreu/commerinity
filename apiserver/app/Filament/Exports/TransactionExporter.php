<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('wallet.id'),
            ExportColumn::make('transactionable_type'),
            ExportColumn::make('transactionable_id'),
            ExportColumn::make('type'),
            ExportColumn::make('status'),
            ExportColumn::make('amount'),
            ExportColumn::make('fee'),
            ExportColumn::make('tax'),
            ExportColumn::make('net_amount'),
            ExportColumn::make('currency'),
            ExportColumn::make('payment_method'),
            ExportColumn::make('checkout_type'),
            ExportColumn::make('integration.name'),
            ExportColumn::make('provider_order_id'),
            ExportColumn::make('provider_transaction_id'),
            ExportColumn::make('provider_signature'),
            ExportColumn::make('provider_gen_id'),
            ExportColumn::make('provider_gen_session'),
            ExportColumn::make('provider_gen_link'),
            ExportColumn::make('provider_gen_qr'),
            ExportColumn::make('provider_generated_sign'),
            ExportColumn::make('qr_code_url'),
            ExportColumn::make('success_url'),
            ExportColumn::make('success_redirect_url'),
            ExportColumn::make('failure_url'),
            ExportColumn::make('failure_redirect_url'),
            ExportColumn::make('verified'),
            ExportColumn::make('verified_at'),
            ExportColumn::make('description'),
            ExportColumn::make('purpose'),
            ExportColumn::make('notes'),
            ExportColumn::make('reference_number'),
            ExportColumn::make('parentTransaction.id'),
            ExportColumn::make('expires_at'),
            ExportColumn::make('balance_after'),
            ExportColumn::make('metadata'),
            ExportColumn::make('provider_response'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
