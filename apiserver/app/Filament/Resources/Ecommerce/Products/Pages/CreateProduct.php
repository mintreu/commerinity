<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\ProductResource;
use App\Services\Ecommerce\ProductManager;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Build filter_options from the form data
        $filterOptions = $this->buildFilterOptions($data);

        // Prepare data for ProductManager
        $productData = [
            'name' => $data['name'],
            'sku' => $data['sku'],
            'url' => $data['url'],
            'status' => $data['status'] ?? ProductStatusCast::DRAFT->value,
            'type' => $data['type'] ?? ProductTypeCast::SIMPLE->value,
            'filter_group_id' => $data['filter_group_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'price' => $data['price'] ?? 0,
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'is_returnable' => $data['is_returnable'] ?? false,
            'return_days' => $data['return_days'] ?? 0,
            'filter_options' => $filterOptions,
        ];

        // Use ProductManager to create product (handles variants automatically)
        $product = ProductManager::create($productData);

        if (! $product) {
            Notification::make()
                ->title('Failed to create product')
                ->danger()
                ->send();

            throw new \Exception('ProductManager::create() returned null');
        }

        Notification::make()
            ->title('Product created')
            ->success()
            ->send();

        return $product;
    }

    /**
     * Build filter_options array from form data
     * Form sends: filter_options[filter_id][option_id] = true
     */
    protected function buildFilterOptions(array $data): array
    {
        if (! isset($data['filter_options']) || ! is_array($data['filter_options'])) {
            return [];
        }

        $filterOptions = [];

        foreach ($data['filter_options'] as $filterId => $options) {
            if (is_array($options)) {
                // Collect selected option IDs
                $selectedOptions = [];
                foreach ($options as $optionId => $isSelected) {
                    if ($isSelected) {
                        $selectedOptions[] = (string) $optionId;
                    }
                }
                if (! empty($selectedOptions)) {
                    $filterOptions[(string) $filterId] = $selectedOptions;
                }
            }
        }

        return $filterOptions;
    }
}
