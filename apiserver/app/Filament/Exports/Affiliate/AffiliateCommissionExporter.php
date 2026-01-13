<?php

namespace App\Filament\Exports\Affiliate;

use App\Models\Affiliate\AffiliateCommission;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AffiliateCommissionExporter extends Exporter
{
    protected static ?string $model = AffiliateCommission::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('user.name'),
            ExportColumn::make('genealogy.id'),
            ExportColumn::make('fromUser.name'),
            ExportColumn::make('commissionable_type'),
            ExportColumn::make('commissionable_id'),
            ExportColumn::make('type'),
            ExportColumn::make('level'),
            ExportColumn::make('rate_percent'),
            ExportColumn::make('base_amount'),
            ExportColumn::make('gross_amount'),
            ExportColumn::make('tds_amount'),
            ExportColumn::make('admin_fee'),
            ExportColumn::make('net_amount'),
            ExportColumn::make('status'),
            ExportColumn::make('paidViaTransaction.id'),
            ExportColumn::make('paid_at'),
            ExportColumn::make('commission_date'),
            ExportColumn::make('period_key'),
            ExportColumn::make('description'),
            ExportColumn::make('metadata'),
            ExportColumn::make('idempotency_key'),
            ExportColumn::make('approved_by'),
            ExportColumn::make('approved_at'),
            ExportColumn::make('reversedCommission.id'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your affiliate commission export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
