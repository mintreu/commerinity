<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('url'),
            ExportColumn::make('type'),
            ExportColumn::make('filterGroup.name'),
            ExportColumn::make('category.name'),
            ExportColumn::make('tenant_type'),
            ExportColumn::make('tenant_id'),
            ExportColumn::make('description'),
            ExportColumn::make('short_description'),
            ExportColumn::make('price'),
            ExportColumn::make('min_quantity'),
            ExportColumn::make('max_quantity'),
            ExportColumn::make('reward_point'),
            ExportColumn::make('is_returnable'),
            ExportColumn::make('parent_id'),
            ExportColumn::make('width'),
            ExportColumn::make('height'),
            ExportColumn::make('length'),
            ExportColumn::make('weight'),
            ExportColumn::make('is_downloadable'),
            ExportColumn::make('download_link'),
            ExportColumn::make('demo_data'),
            ExportColumn::make('file_size'),
            ExportColumn::make('proxy_base_url'),
            ExportColumn::make('internal_auth_key'),
            ExportColumn::make('internal_auth_secret'),
            ExportColumn::make('status'),
            ExportColumn::make('status_feedback'),
            ExportColumn::make('view_count'),
            ExportColumn::make('meta_data'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('tax_code.id'),
            ExportColumn::make('is_tax_inclusive'),
            ExportColumn::make('is_exempted'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
