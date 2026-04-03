<?php

namespace App\Filament\Imports\Ecommerce;

use App\Models\Ecommerce\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Number;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('parent_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->example('Health & Wellness')
                ->rules(['required', 'max:255']),
            ImportColumn::make('url')
                ->requiredMapping()
                ->example('health-wellness')
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['boolean']),
            ImportColumn::make('view_count')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('order')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['integer']),
            ImportColumn::make('desc'),
            ImportColumn::make('seo_meta'),
            ImportColumn::make('tax_slab')
                ->rules(['max:255']),
            ImportColumn::make('meta_data'),
            ImportColumn::make('banners'),
            ImportColumn::make('category_image_id')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing categories by URL')
                ->default(true),
        ];
    }

    public function resolveRecord(): ?Category
    {
        if ($this->options['updateExisting'] ?? true) {
            return Category::firstOrNew([
                'url' => $this->data['url'],
            ]);
        }

        return new Category();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your category import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
