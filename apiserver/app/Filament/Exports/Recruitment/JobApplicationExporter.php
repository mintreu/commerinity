<?php

namespace App\Filament\Exports\Recruitment;

use App\Models\Recruitment\JobApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class JobApplicationExporter extends Exporter
{
    protected static ?string $model = JobApplication::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('recruitment.title'),
            ExportColumn::make('applicant_type'),
            ExportColumn::make('applicant_id'),
            ExportColumn::make('guardian_name'),
            ExportColumn::make('address.title'),
            ExportColumn::make('educations'),
            ExportColumn::make('skills'),
            ExportColumn::make('experiences'),
            ExportColumn::make('reference_name'),
            ExportColumn::make('reference_contact'),
            ExportColumn::make('is_paid'),
            ExportColumn::make('amount'),
            ExportColumn::make('transaction.id'),
            ExportColumn::make('status'),
            ExportColumn::make('status_feedback'),
            ExportColumn::make('submitted_at'),
            ExportColumn::make('import_batch_id'),
            ExportColumn::make('import_data'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your job application export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
