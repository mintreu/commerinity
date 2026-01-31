<?php

namespace App\Filament\Exports\Ecommerce;

use App\Models\Ecommerce\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('parent.name'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('url'),
            ExportColumn::make('type'),
            ExportColumn::make('filterGroup.name'),
            ExportColumn::make('category.name'),
            ExportColumn::make('description'),
            ExportColumn::make('seo_meta'),
            ExportColumn::make('short_description'),
            ExportColumn::make('product_display_id'),
            ExportColumn::make('price')
                ->state(fn (Product $record) => $record->getPrice()),
            ExportColumn::make('status'),
            ExportColumn::make('is_returnable'),
            ExportColumn::make('return_days'),
            ExportColumn::make('view_count'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
