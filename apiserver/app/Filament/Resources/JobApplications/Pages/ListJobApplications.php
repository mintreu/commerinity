<?php

declare(strict_types=1);

namespace App\Filament\Resources\JobApplications\Pages;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Filament\Resources\JobApplications\Schemas\ImportSchema;
use App\Imports\EnhancedBulkJobApplicationImport;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListJobApplications extends ListRecords
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->slideOver()
                ->color('warning')
                ->use(EnhancedBulkJobApplicationImport::class)
                ->sampleExcel(
                    sampleData: ImportSchema::getExcelColumns(),
                    fileName: 'job_application_import_template.xlsx',
                    sampleButtonLabel: 'Download Excel Template',
                    customiseActionUsing: fn(\Filament\Forms\Components\Actions\Action $action) => $action
                        ->color('success')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->requiresConfirmation(),
                ),
            CreateAction::make(),
        ];
    }
}
