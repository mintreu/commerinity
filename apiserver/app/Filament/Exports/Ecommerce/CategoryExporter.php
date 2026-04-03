<?php

namespace App\Filament\Exports\Ecommerce;

use App\Models\Ecommerce\Category;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CategoryExporter extends Exporter
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('parent_id'),
            ExportColumn::make('name'),
            ExportColumn::make('url'),
            ExportColumn::make('status'),
            ExportColumn::make('view_count'),
            ExportColumn::make('order'),
            ExportColumn::make('desc'),
            ExportColumn::make('seo_meta'),
            ExportColumn::make('tax_slab'),
            ExportColumn::make('meta_data'),
            ExportColumn::make('banners'),
            ExportColumn::make('category_image_id'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your category export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
