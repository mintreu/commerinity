<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\FilterOption;
use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Product Management Service
 *
 * Handles complete CRUD operations for products including:
 * - Simple products
 * - Configurable products with variants
 * - Automatic variant generation and management
 * - Cascading deletion for configurable products
 *
 * Usage:
 *   ProductManager::create([...data...]);
 *   ProductManager::update($product, [...data...]);
 *   ProductManager::delete($product);
 */
final class ProductManager
{
    /**
     * Create a new product (Simple or Configurable)
     *
     * @param  array  $data  Product data including:
     *                       - name, sku, url, status, type, filter_group_id, category_id, price
     *                       - description, short_description (optional)
     *                       - filter_options (optional): [filter_id => [option_ids]]
     * @param  bool  $reload  Whether to return fresh model with relationships
     */
    public static function create(array $data, bool $reload = false): ?Product
    {
        return DB::transaction(function () use ($data, $reload) {
            try {
                $type = $data['type'] instanceof ProductTypeCast
                    ? $data['type']->value
                    : $data['type'];

                if (! ProductTypeCast::validate($type)) {
                    throw new \InvalidArgumentException("Invalid product type: {$type}");
                }

                $case = ProductTypeCast::tryFrom($type);

                $filterOptions = $data['filter_options'] ?? [];
                unset($data['filter_options']);

                $record = Product::create(array_merge($data, [
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'url' => $data['url'],
                    'status' => $data['status'] ?? ProductStatusCast::DRAFT,
                    'type' => $case->value,
                    'filter_group_id' => $data['filter_group_id'],
                    'category_id' => $data['category_id'] ?? null,
                    'price' => $data['price'] ?? 0,
                    'gst_tax_type' => $data['gst_tax_type'] ?? null,
                    'description' => $data['description'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'is_returnable' => $data['is_returnable'] ?? false,
                    'return_days' => $data['return_days'] ?? 0,
                ]));

                if (! empty($filterOptions)) {
                    $instance = new self;

                    if (in_array($case->value, [ProductTypeCast::SIMPLE->value, ProductTypeCast::WHOLESALE->value])) {
                        $instance->attachFilterOptionsToProduct($record, $filterOptions);
                    } elseif ($case->value === ProductTypeCast::CONFIGURABLE->value) {
                        $instance->attachFilterOptionsToParent($record, $filterOptions);
                        $instance->generateProductVariants($record, $data, $filterOptions);
                    }
                }

                return $reload ? $record->fresh() : $record;

            } catch (\Throwable $t) {
                Log::error('Product creation failed', [
                    'error' => $t->getMessage(),
                    'trace' => $t->getTraceAsString(),
                    'data' => $data,
                ]);
                throw $t;
            }
        });
    }

    /**
     * Update an existing product
     *
     * @param  array  $data  Update data
     * @param  bool  $reload  Whether to return fresh model
     */
    public static function update(Model|Product $product, array $data, bool $reload = false): ?Product
    {
        return DB::transaction(function () use ($product, $data, $reload) {
            try {
                $type = $data['type'] instanceof ProductTypeCast
                    ? $data['type']->value
                    : ($data['type'] ?? $product->type);

                if (! ProductTypeCast::validate($type)) {
                    throw new \InvalidArgumentException("Invalid product type: {$type}");
                }

                $case = ProductTypeCast::tryFrom($type);

                $recreateVariants = isset($data['filter_group_id'])
                    && $product->filter_group_id != $data['filter_group_id'];

                $filterOptions = $data['filter_options'] ?? [];
                unset($data['filter_options']);

                $product->update(array_merge($data, [
                    'type' => $type,
                    'price' => $data['price'] ?? $product->price,
                    'gst_tax_type' => $data['gst_tax_type'] ?? $product->gst_tax_type,
                ]));

                $instance = new self;

                if ($case->value === ProductTypeCast::CONFIGURABLE->value) {
                    if ($recreateVariants) {
                        $product->variants()->each(fn ($variant) => $variant->delete());

                        if (! empty($filterOptions)) {
                            $instance->attachFilterOptionsToParent($product, $filterOptions);
                            $instance->generateProductVariants($product, $data, $filterOptions);
                        }
                    } else {
                        if (! empty($filterOptions)) {
                            $instance->smartUpdateVariants($product, $data, $filterOptions);
                        }
                    }
                    $instance->updateProductFilterOptionToParent($product, $filterOptions);
                } else {
                    if (! empty($filterOptions)) {
                        $instance->updateProductFilterOption($product, $filterOptions);
                    }
                }

                return $reload ? $product->fresh() : $product;

            } catch (\Throwable $t) {
                Log::error('Product update failed', [
                    'error' => $t->getMessage(),
                    'product_id' => $product->id,
                    'data' => $data,
                ]);
                throw $t;
            }
        });
    }

    /**
     * Delete a product and handle cascading deletions
     */
    public static function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            try {
                $productId = $product->id;
                $productSku = $product->sku;
                $productType = $product->type instanceof ProductTypeCast
                    ? $product->type->value
                    : $product->type;

                if ($productType === ProductTypeCast::CONFIGURABLE->value) {
                    $variantIds = $product->variants()->pluck('id')->toArray();

                    $product->variants()->each(fn ($variant) => $variant->delete());

                    Log::info('Product variants deleted', [
                        'parent_product_id' => $productId,
                        'parent_sku' => $productSku,
                        'variant_count' => count($variantIds),
                        'variant_ids' => $variantIds,
                    ]);
                }

                $deleted = $product->delete();

                if ($deleted) {
                    Log::info('Product deleted successfully', [
                        'product_id' => $productId,
                        'sku' => $productSku,
                        'type' => $productType,
                    ]);
                }

                return $deleted;

            } catch (\Throwable $t) {
                Log::error('Product deletion failed', [
                    'error' => $t->getMessage(),
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                ]);
                throw $t;
            }
        });
    }

    /**
     * Generate and create product variants for configurable product
     */
    private function generateProductVariants(Product $product, array $productData, array $filterOptions): Product
    {
        if (! empty($filterOptions)) {
            $variants = $this->generateVariants($productData['sku'] ?? $product->sku, $filterOptions);
            $this->createVariants($product, $productData, $variants);
        }

        return $product;
    }

    /**
     * Attach filter options to parent configurable product
     */
    private function attachFilterOptionsToParent(Product $product, array $filterOptions): void
    {
        $pivotData = collect($filterOptions)
            ->reduce(function (array $carry, $optionIds, $filterId) {
                foreach ($optionIds as $optionId) {
                    $carry[$optionId] = ['filter_id' => $filterId];
                }
                return $carry;
            }, []);

        if (! empty($pivotData)) {
            $product->filterOptions()->attach($pivotData);
        }
    }

    /**
     * Attach filter options to product (simple/variant)
     */
    private function attachFilterOptionsToProduct(Product $product, array $filterOptions): void
    {
        $firstArrayKeyIsNumber = is_numeric(array_key_first($filterOptions));

        foreach ($filterOptions as $key => $ids) {
            if (is_array($ids)) {
                foreach ($ids as $option) {
                    $optionId = $this->getOptionId($product, $option, $firstArrayKeyIsNumber);
                    $filterId = $firstArrayKeyIsNumber
                        ? FilterOption::find($optionId)?->filter_id
                        : $key;

                    if ($optionId && $filterId) {
                        $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
                    }
                }
            } else {
                $optionId = $this->getOptionId($product, $ids, $firstArrayKeyIsNumber);
                $filterId = $firstArrayKeyIsNumber
                    ? FilterOption::find($optionId)?->filter_id
                    : $key;

                if ($optionId && $filterId) {
                    $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
                }
            }
        }
    }

    private function getOptionId(Product $product, array|object|int $option, bool $isVariant = false): ?int
    {
        if (is_object($option)) {
            return $option->id;
        }

        if (is_array($option)) {
            if ($isVariant) {
                return $product->type == ProductTypeCast::CONFIGURABLE ? $option[0] : $option;
            }

            return $option['id'] ?? $option[0] ?? null;
        }

        return $option;
    }

    /**
     * Generate all possible variants based on selected filters (Cartesian Product)
     */
    private function generateVariants(string $initialSku, array $filters): array
    {
        $filterOptionsWithValues = [];
        foreach ($filters as $filterId => $optionIds) {
            $options = FilterOption::whereIn('id', $optionIds)
                ->pluck('value', 'id')
                ->map(fn ($value) => trim(str_replace('GSM', '', $value)))
                ->toArray();
            $filterOptionsWithValues[$filterId] = $options;
        }

        $combinations = $this->cartesianProduct($filterOptionsWithValues);

        return array_map(function ($combination) use ($initialSku) {
            $optionValues = implode('-', array_values($combination));

            return [
                'sku' => strtoupper("{$initialSku}-{$optionValues}"),
                'url' => strtolower(str_replace(' ', '-', "{$initialSku}-{$optionValues}")),
                'filter_option_ids' => array_keys($combination),
            ];
        }, $combinations);
    }

    /**
     * Create variant products
     */
    private function createVariants(Product $parentProduct, array $productData, array $variants): void
    {
        $allSkus = collect($variants)->pluck('sku')->toArray();
        $existingSkus = Product::whereIn('sku', $allSkus)->pluck('sku')->all();

        $newVariants = collect($variants)
            ->reject(fn ($variant) => in_array($variant['sku'], $existingSkus))
            ->values();

        foreach ($newVariants as $variant) {
            $variantProduct = Product::create([
                'parent_id' => $parentProduct->id,
                'type' => ProductTypeCast::SIMPLE->value,
                'name' => $parentProduct->name,
                'sku' => $variant['sku'],
                'url' => $variant['url'],
                'status' => $parentProduct->status,
                'description' => $parentProduct->description ?? null,
                'short_description' => $parentProduct->short_description ?? null,
                'filter_group_id' => $parentProduct->filter_group_id,
                'category_id' => $parentProduct->category_id,
                'min_quantity' => $parentProduct->min_quantity ?? 1,
                'max_quantity' => $parentProduct->max_quantity ?? 1,
                'price' => $parentProduct->price ?? 0,
                'gst_tax_type' => $parentProduct->gst_tax_type,
                'is_returnable' => $parentProduct->is_returnable,
                'return_days' => $parentProduct->return_days,
            ]);

            $this->attachFilterOptionsToProduct($variantProduct, $variant['filter_option_ids']);
        }
    }

    /**
     * Generates the Cartesian Product of filter options
     */
    private function cartesianProduct(array $arrays): array
    {
        if (empty($arrays)) {
            return [[]];
        }

        $result = [[]];
        foreach ($arrays as $options) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($options as $optionId => $optionValue) {
                    $newResult[] = $existing + [$optionId => $optionValue];
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    /**
     * Update filter options for simple products
     */
    private function updateProductFilterOption(Product $product, array $filterOptions): void
    {
        $pivotData = [];
        foreach ($filterOptions as $filterId => $filterOptionId) {
            $pivotData[$filterOptionId] = ['filter_id' => $filterId];
        }

        $product->filterOptions()->sync($pivotData);
    }

    /**
     * Update filter options for configurable parent product
     */
    private function updateProductFilterOptionToParent(Product $product, array $filterOptions): void
    {
        $pivotData = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $pivotData[$optionId] = ['filter_id' => $filterId];
            }
        }

        $product->filterOptions()->sync($pivotData);
    }

    /**
     * Smart variant update - Only add/remove changed variants
     */
    private function smartUpdateVariants(Product $product, array $productData, array $filterOptions): void
    {
        $newOptionIds = collect($filterOptions)
            ->flatten()
            ->map(fn ($v) => (int) $v)
            ->sort()
            ->values()
            ->all();

        $existingOptionIds = $product->variants()
            ->with('filterOptions')
            ->get()
            ->flatMap(fn ($variant) => $variant->filterOptions->pluck('id'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($newOptionIds === $existingOptionIds) {
            return;
        }

        $newVariants = $this->generateVariants($productData['sku'] ?? $product->sku, $filterOptions);
        $existingVariants = $product->variants()->with('filterOptions')->get();

        $existingSignatures = $existingVariants->mapWithKeys(function ($variant) {
            $optionIds = $variant->filterOptions->pluck('id')->sort()->values()->all();
            $signature = implode('-', $optionIds);

            return [$signature => $variant];
        });

        $newSignatures = collect($newVariants)->mapWithKeys(function ($variant) {
            $signature = implode('-', collect($variant['filter_option_ids'])->sort()->values()->all());

            return [$signature => $variant];
        });

        $toDelete = $existingSignatures->keys()->diff($newSignatures->keys());
        foreach ($toDelete as $signature) {
            $existingSignatures[$signature]->delete();
        }

        $toCreate = $newSignatures->keys()->diff($existingSignatures->keys());
        $variantsToCreate = $toCreate->map(fn ($signature) => $newSignatures[$signature])->values()->all();

        if (! empty($variantsToCreate)) {
            $this->createVariants($product, $product->toArray(), $variantsToCreate);
        }
    }
}
