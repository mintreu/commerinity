<?php

namespace Mintreu\LaravelProductCatalogue\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterOption;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;


/**
 * Universal Product Management Service
 *
 * Handles complete CRUD operations for products including:
 * - Simple products
 * - Wholesale products
 * - Configurable products with variants
 * - Automatic variant generation and management
 * - Cascading deletion for configurable products
 *
 * @package Mintreu\LaravelProductCatalogue\Services
 */
final class BackupProductManager
{

    /**
     * Create a new product (Simple, Wholesale, or Configurable)
     *
     * @param array $data Product data including filter_options
     * @param bool $reload Whether to reload the product with relationships (default: false)
     * @return Product|null Returns null if product type is invalid
     * @throws \Throwable Throws exception if creation fails with detailed error info
     */
    public static function create(array $data, bool $reload = false): ?Product
    {
        return DB::transaction(function () use ($data, $reload) {
            try {
                // Validate product type
                if (!ProductTypeCast::validate($data['type'])) {
                    throw new \InvalidArgumentException("Invalid product type: {$data['type']}");
                }

                $case = ProductTypeCast::tryFrom($data['type']);

                // Extract and remove filter options from data
                $filterOptions = $data['filter_options'] ?? [];
                unset($data['filter_options']);

                // Create product record
                $record = Product::create(array_merge($data,[
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'url' => $data['url'],
                    'status' => $data['status'] ?? PublishableStatusCast::DRAFT,
                    'type' => $case->value,
                    'filter_group_id' => $data['filter_group_id'],
                    'price' => $data['price'] ?? 0,
                    'reward_point' => $data['reward_point'] ?? 0,
                ]));

                // Attach filter options based on product type
                if (!empty($filterOptions)) {
                    $instance = new self();

                    if (in_array($case->value, [ProductTypeCast::SIMPLE->value, ProductTypeCast::WHOLESALE->value])) {
                        // For simple/wholesale: attach filter options to product
                        $instance->attachFilterOptionsToProduct($record, $filterOptions);
                    } elseif ($case->value === ProductTypeCast::CONFIGURABLE->value) {
                        // For configurable: attach to parent and generate variants
                        $instance->attachFilterOptionsToParent($record, $filterOptions);
                        $instance->generateProductVariants($record, $data, $filterOptions);
                    }
                }

                // Return fresh instance only if explicitly requested
                return $reload ? $record->fresh() : $record;

            } catch (\InvalidArgumentException $e) {
                // Log validation errors
                Log::error('Product creation failed - Invalid argument', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);
                throw $e;

            } catch (\Illuminate\Database\QueryException $e) {
                // Log database errors
                Log::error('Product creation failed - Database error', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'data' => $data,
                ]);
                throw new \RuntimeException("Failed to create product: {$e->getMessage()}", 0, $e);

            } catch (\Throwable $t) {
                // Log unexpected errors
                Log::error('Product creation failed - Unexpected error', [
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
     * @param Product $product Product instance to update
     * @param array $data Update data including filter_options
     * @param bool $reload Whether to reload the product with relationships (default: false)
     * @return Product|null Returns null if product type is invalid
     * @throws \Throwable Throws exception if update fails with detailed error info
     */
    public static function update(Product $product, array $data, bool $reload = false): ?Product
    {
        return DB::transaction(function () use ($product, $data, $reload) {
            try {
                // Validate product type
                if (!ProductTypeCast::validate($data['type'])) {
                    throw new \InvalidArgumentException("Invalid product type: {$data['type']}");
                }

                $case = ProductTypeCast::tryFrom($data['type']);

                // Check if filter group changed (triggers full variant recreation)
                $recreateVariants = isset($data['filter_group_id']) &&
                    $product->filter_group_id != $data['filter_group_id'];

                // Extract and remove filter options from data
                $filterOptions = $data['filter_options'] ?? [];
                unset($data['filter_options']);

                // Update product data
                $product->update($data);

                // Create instance for non-static method calls
                $instance = new self();

                // Handle configurable product variant updates
                if ($case->value === ProductTypeCast::CONFIGURABLE->value) {
                    if ($recreateVariants) {
                        // Filter group changed - delete all variants and recreate
                        $product->variants()->each(fn($variant) => $variant->delete());

                        // Regenerate variants with new filter group
                        if (!empty($filterOptions)) {
                            $instance->attachFilterOptionsToParent($product, $filterOptions);
                            $instance->generateProductVariants($product, $data, $filterOptions);
                        }
                    } else {
                        // Smart update: only add/remove changed variants
                        if (!empty($filterOptions)) {
                            $instance->smartUpdateVariants($product,$filterOptions);
                        }
                    }
                    $this->updateProductFilterOptionToParent($product,$filterOptions);
                }else{
                    // Update filter options for all product types
                    if (!empty($filterOptions)) {
                        $instance->updateProductFilterOption($product, $filterOptions);
                    }
                }



                // Return fresh instance only if explicitly requested
                return $reload ? $product->fresh() : $product;

            } catch (\InvalidArgumentException $e) {
                // Log validation errors
                Log::error('Product update failed - Invalid argument', [
                    'error' => $e->getMessage(),
                    'product_id' => $product->id,
                    'data' => $data,
                ]);
                throw $e;

            } catch (\Illuminate\Database\QueryException $e) {
                // Log database errors
                Log::error('Product update failed - Database error', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'product_id' => $product->id,
                    'data' => $data,
                ]);
                throw new \RuntimeException("Failed to update product: {$e->getMessage()}", 0, $e);

            } catch (\Throwable $t) {
                // Log unexpected errors
                Log::error('Product update failed - Unexpected error', [
                    'error' => $t->getMessage(),
                    'trace' => $t->getTraceAsString(),
                    'product_id' => $product->id,
                    'data' => $data,
                ]);
                throw $t;
            }
        });
    }


    /**
     * Delete a product and handle cascading deletions
     *
     * For configurable products:
     * - Deletes all child variants first
     * - Then deletes the parent product
     *
     * For simple/wholesale products:
     * - Direct deletion
     *
     * @param Product $product Product instance to delete
     * @return bool Returns true if deletion was successful
     * @throws \Throwable Throws exception if deletion fails with detailed error info
     */
    public static function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            try {
                // Store product info for logging before deletion
                $productId = $product->id;
                $productSku = $product->sku;
                $productType = $product->type->value;

                // If configurable product, delete all variants first
                if ($productType === ProductTypeCast::CONFIGURABLE->value) {
                    $variantIds = $product->variants()->pluck('id')->toArray();

                    $product->variants()->each(function ($variant) {
                        $variant->delete();
                    });

                    Log::info('Product variants deleted', [
                        'parent_product_id' => $productId,
                        'parent_sku' => $productSku,
                        'variant_count' => count($variantIds),
                        'variant_ids' => $variantIds,
                    ]);
                }

                // Delete the main product
                $deleted = $product->delete();

                if ($deleted) {
                    Log::info('Product deleted successfully', [
                        'product_id' => $productId,
                        'sku' => $productSku,
                        'type' => $productType,
                    ]);
                }

                return $deleted;

            } catch (\Illuminate\Database\QueryException $e) {
                // Log database errors
                Log::error('Product deletion failed - Database error', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                ]);
                throw new \RuntimeException("Failed to delete product: {$e->getMessage()}", 0, $e);

            } catch (\Throwable $t) {
                // Log unexpected errors
                Log::error('Product deletion failed - Unexpected error', [
                    'error' => $t->getMessage(),
                    'trace' => $t->getTraceAsString(),
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                ]);
                throw $t;
            }
        });
    }




    /**
     * PRIVATES METHODS FOR SUPPORT THIS CLASS
     */


    // Private Methods


    /**
     * Attach filter options to parent configurable product
     *
     * Uses bulk insert for optimal performance instead of multiple attach calls.
     * Transforms filter options array into pivot data for batch insertion.
     *
     * @param Product $product Parent configurable product
     * @param array $filterOptions Array of filter_id => [option_ids]
     * @return void
     */
    private function attachFilterOptionsToParent(Product $product, array $filterOptions): void
    {
        // new you build
        // Build pivot data array with filter_id for bulk insertion
        $pivotData = collect($filterOptions)
            ->reduce(function ($carry, $optionIds, $filterId) {
                foreach ($optionIds as $optionId) {
                    $carry[$optionId] = ['filter_id' => $filterId];
                }
                return $carry;
            }, []);


        // Single bulk attach operation instead of multiple individual attaches
        if (!empty($pivotData)) {
            $product->filterOptions()->attach($pivotData);
        }

    }



    private function updateProductFilterOption(Product $product, array $filterOptions)
    {
        // Build pivot array with filter_id
        $pivotData = [];
        foreach ($filterOptions as $filterId => $filterOptionId) {
            $pivotData[$filterOptionId] = ['filter_id' => $filterId];
        }

        // Sync with extra pivot data
        $product->filterOptions()->sync($pivotData);
    }

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


    private  function attachFilterOptionsToProduct(Product $product, array $filterOptions)
    {
        $firstArrayKeyIsNumber = is_numeric(array_key_first($filterOptions));
        $optionId = null;

        foreach ($filterOptions as $key => $ids)
        {
            if (is_array($ids))
            {
                foreach ($ids as $option)
                {
                    $optionId = $this->getOptionId($product,$option,$firstArrayKeyIsNumber);
                }

            }else{
                $optionId = $this->getOptionId($product,$option,$firstArrayKeyIsNumber);
            }

            $filterId = $firstArrayKeyIsNumber ? FilterOption::find($optionId)->filter_id : $key;

            $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
        }


    }

    private function getOptionId(Product $product,array|object $option,bool $isVariant = false)
    {
        // Ensure we're working with just the ID
        if (is_object($option)) {
            return $option->id;
        } elseif (is_array($option)) {
            if ($isVariant)
            {
                return $product->type == ProductTypeCast::CONFIGURABLE ? $option[0] : $option;
            }else{
                return $optionId['id'] ?? $option[0];
            }
        }
        return null;
    }



    private  function generateProductVariants(Product $product, array $filterOption)
    {
        // Generate and create variants
        if (! empty($filterOption)) {
            $variants = $this->generateVariants($product->sku, $filterOption);
            $this->createVariants($product, $variants);
        }
    }



    private function generateVariants(string $sku, mixed $filterOptions)
    {
        // Get all selected filter option IDs and their corresponding values
        $filterOptionsWithValues = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            $options = FilterOption::whereIn('id', $optionIds)
                ->pluck('value', 'id')
                ->map(function ($value) {
                    // Clean up the value by removing 'GSM' and any extra spaces
                    return trim(str_replace('GSM', '', $value));
                })
                ->toArray();
            $filterOptionsWithValues[$filterId] = $options;
        }

        // Generate Cartesian product
        $combinations = $this->cartesianProduct($filterOptionsWithValues);

        // Format variants with unique SKUs
        return array_map(function ($combination) use ($sku) {
            $optionValues = implode('-', array_values($combination));

            return [
                'sku' => strtoupper("{$sku}-{$optionValues}"),
                'url' => strtolower(str_replace(' ', '-', "{$sku}-{$optionValues}")),
                'filter_option_ids' => array_keys($combination),
            ];
        }, $combinations);
    }

    private function createVariants(Product $product, null|array $variants)
    {
        // Collect all desired SKUs from the generated variants
        $allSkus = collect($variants)->pluck('sku')->toArray();

        // Fetch existing products by SKU
        $existingSkus = Product::whereIn('sku', $allSkus)->pluck('sku')->all();

        // Filter only the variants that do NOT exist
        $newVariants = collect($variants)
            ->reject(fn($variant) => in_array($variant['sku'], $existingSkus))
            ->values();

        // Create only new variants
        foreach ($newVariants as $variant) {
            $variantProduct = Product::create([
                'parent_id' => $product->id,
                'type' => 'simple',
                'name' => $product['name'],
                'sku' => $variant['sku'],
                'url' => $variant['url'],
                'status' => $product->status,
                'description' => $productData['description'] ?? null,
                'filter_group_id' => $productData['filter_group_id'],
                'category_id' => $productData['category_id'] ?? null,
                'min_quantity' => $product->min_quantity,
                'max_quantity' => $product->max_quantity,
                'price'        => $product->price,
                'reward_point' => $product->reward_point
            ]);

            // Attach filter options to the new variant
            $this->attachFilterOptionsToProduct($variantProduct, $variant['filter_option_ids']);
        }
    }




    private function cartesianProduct(array $filterOptionsWithValues): array
    {
        if (empty($filterOptionsWithValues)) {
            return [[]];
        }

        $result = [[]];
        foreach ($filterOptionsWithValues as $options) {
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


    private function smartUpdateVariants(Product $product,array $filterOptions)
    {

        // 1. Gather new filter option IDs from the form data
        $newOptionIds = collect($filterOptions)
            ->flatten()
            ->map(fn($v) => (int) $v) // Normalize
            ->sort()
            ->values()
            ->all();

        // 2. Gather all unique filter option IDs used by existing variants
        $existingOptionIds = $product->variants()
            ->with('filterOptions')
            ->get()
            ->flatMap(fn($variant) => $variant->filterOptions->pluck('id'))
            ->unique()
            ->sort()
            ->values()
            ->all();

//        dd($newOptionIds === $existingOptionIds);

        // 3. Compare both sets
        if ($newOptionIds === $existingOptionIds) {
            // No change — skip processing
            return;
        }

        // 4. There’s a difference — proceed with diffing variant combinations
        $newVariants = $this->generateVariants($data['sku'] ?? $product->sku, $product['filter_options']);

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

        // 5. Delete outdated variants
        $toDelete = $existingSignatures->keys()->diff($newSignatures->keys());
        foreach ($toDelete as $signature) {
            $existingSignatures[$signature]->delete();
        }

        // 6. Create only new variants
        $toCreate = $newSignatures->keys()->diff($existingSignatures->keys());
        $variantsToCreate = $toCreate->map(fn($signature) => $newSignatures[$signature])->values()->all();

        if (!empty($variantsToCreate)) {
            $this->createVariants($product, $variantsToCreate);
        }

    }


}
