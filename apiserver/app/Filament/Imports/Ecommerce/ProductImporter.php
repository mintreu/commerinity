<?php

namespace App\Filament\Imports\Ecommerce;

use App\Models\Ecommerce\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->example('Sample Product')
                ->rules(['required', 'max:255']),
            ImportColumn::make('sku')
                ->label('SKU')
                ->requiredMapping()
                ->example('SKU-1001')
                ->rules(['required', 'max:255']),
            ImportColumn::make('url')
                ->requiredMapping()
                ->example('sample-product')
                ->rules(['required', 'max:255']),
            ImportColumn::make('type')
                ->requiredMapping()
                ->example('product')
                ->rules(['required', 'max:255']),
            ImportColumn::make('filterGroup')
                ->relationship()
                ->helperText('Matches filter group by its record title in Filament.')
                ->ignoreBlankState(),
            ImportColumn::make('category')
                ->relationship()
                ->helperText('Matches category by its record title in Filament.')
                ->ignoreBlankState(),
            ImportColumn::make('description'),
            ImportColumn::make('seo_meta'),
            ImportColumn::make('short_description'),
            ImportColumn::make('price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('bv')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('pv')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('reward_points')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('min_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('max_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('wholesale_unit_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('weight_grams')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('length_cm')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('width_cm')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('height_cm')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('is_commissionable')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['boolean']),
            ImportColumn::make('commission_rate')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['numeric']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->example('published')
                ->rules(['required', 'max:255']),
            ImportColumn::make('is_returnable')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['boolean']),
            ImportColumn::make('return_days')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('view_count')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing products by SKU')
                ->default(true),
        ];
    }

    public function resolveRecord(): ?Product
    {
        if ($this->options['updateExisting'] ?? true) {
            return Product::firstOrNew([
                'sku' => $this->data['sku'],
            ]);
        }

        return new Product();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
