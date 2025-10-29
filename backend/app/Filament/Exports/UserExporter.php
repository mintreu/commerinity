<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('email_verified_at'),
            ExportColumn::make('mobile'),
            ExportColumn::make('mobile_verified_at'),
            ExportColumn::make('referral_code'),
            ExportColumn::make('parent_id'),
            ExportColumn::make('originator_type'),
            ExportColumn::make('originator_id'),
            ExportColumn::make('bio'),
            ExportColumn::make('gender'),
            ExportColumn::make('dob'),
            ExportColumn::make('type'),
            ExportColumn::make('status'),
            ExportColumn::make('status_feedback'),
            ExportColumn::make('onboarded'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('level.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
