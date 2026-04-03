<?php

namespace App\Filament\Exports\Membership;

use App\Models\Membership\UserSubscription;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class UserSubscriptionExporter extends Exporter
{
    protected static ?string $model = UserSubscription::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('user.name'),
            ExportColumn::make('stage.name'),
            ExportColumn::make('level.name'),
            ExportColumn::make('currentLevel.name'),
            ExportColumn::make('level_achieved_at'),
            ExportColumn::make('highestLevel.name'),
            ExportColumn::make('qualification_snapshot'),
            ExportColumn::make('personal_pv'),
            ExportColumn::make('team_pv'),
            ExportColumn::make('total_commission_earned'),
            ExportColumn::make('current_month_commission'),
            ExportColumn::make('last_renewed_at'),
            ExportColumn::make('renewal_count'),
            ExportColumn::make('base_price'),
            ExportColumn::make('discount'),
            ExportColumn::make('tax_amount'),
            ExportColumn::make('amount'),
            ExportColumn::make('is_paid'),
            ExportColumn::make('paid_at'),
            ExportColumn::make('transaction.id'),
            ExportColumn::make('wallet.id'),
            ExportColumn::make('starts_at'),
            ExportColumn::make('expires_at'),
            ExportColumn::make('status'),
            ExportColumn::make('previousSubscription.id'),
            ExportColumn::make('sponsor_type'),
            ExportColumn::make('sponsor_id'),
            ExportColumn::make('metadata'),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user subscription export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
