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
final class ProductManager
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
                $type = $data['type'] instanceof ProductTypeCast ? $data['type']->value : $data['type'];
                if (!ProductTypeCast::validate($type)) {
                    throw new \InvalidArgumentException("Invalid product type: {$type}");
                }

                $case = ProductTypeCast::tryFrom($type);

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
                    // Update parent product filter options
                    $instance->updateProductFilterOptionToParent($product,$filterOptions);
                }else{
                    // Update filter options for simple/wholesale products
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
     * =====================================================================
     * PRIVATE SUPPORT METHODS
     * =====================================================================
     */


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
        // Build pivot data array with filter_id for bulk insertion
        // Uses reduce to preserve option IDs as array keys
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


    /**
     * Update filter options for simple/wholesale products
     *
     * Syncs filter options with pivot data containing filter_id.
     * Removes old associations and creates new ones in a single operation.
     *
     * @param Product $product Simple or wholesale product to update
     * @param array $filterOptions Array of filter_id => option_id
     * @return void
     */
    private function updateProductFilterOption(Product $product, array $filterOptions): void
    {
        // Build pivot array with filter_id
        $pivotData = [];
        foreach ($filterOptions as $filterId => $filterOptionId) {
            $pivotData[$filterOptionId] = ['filter_id' => $filterId];
        }

        // Sync with extra pivot data
        $product->filterOptions()->sync($pivotData);
    }


    /**
     * Update filter options for configurable parent product
     *
     * Syncs all filter options for the parent configurable product.
     * Handles multiple option IDs per filter.
     *
     * @param Product $product Configurable parent product
     * @param array $filterOptions Array of filter_id => [option_ids]
     * @return void
     */
    private function updateProductFilterOptionToParent(Product $product, array $filterOptions): void
    {
        // Build pivot data for all filter options
        $pivotData = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $pivotData[$optionId] = ['filter_id' => $filterId];
            }
        }

        // Sync all filter options in single operation
        $product->filterOptions()->sync($pivotData);
    }


    /**
     * Attach filter options to product (simple/wholesale/variant)
     *
     * Handles two data formats:
     * - Numeric keys: Array of option IDs (used for variants)
     * - Filter ID keys: Mapping of filter_id => option_ids (used for simple/wholesale)
     *
     * @param Product $product Product to attach filter options to
     * @param array $filterOptions Filter options in various formats
     * @return void
     */
    private  function attachFilterOptionsToProduct(Product $product, array $filterOptions): void
    {
        // Determine data format by checking first array key
        $firstArrayKeyIsNumber = is_numeric(array_key_first($filterOptions));
        $optionId = null;

        // Iterate through filter options
        foreach ($filterOptions as $key => $ids)
        {
            // Handle array of option IDs
            if (is_array($ids))
            {
                foreach ($ids as $option)
                {
                    $optionId = $this->getOptionId($product,$option,$firstArrayKeyIsNumber);
                }

            }else{
                // Handle single option ID
                $optionId = $this->getOptionId($product,$ids,$firstArrayKeyIsNumber);
            }

            // Determine filter ID based on data format
            // For variants: lookup filter from option
            // For simple/wholesale: use array key as filter ID
            $filterId = $firstArrayKeyIsNumber ? FilterOption::find($optionId)->filter_id : $key;

            // Attach option with pivot data
            $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
        }


    }


    /**
     * Extract option ID from various data formats
     *
     * Handles objects, arrays, and different data structures coming from
     * factories, seeders, and form data.
     *
     * @param Product $product Product being processed
     * @param array|object $option Option data in various formats
     * @param bool $isVariant Whether processing variant data
     * @return int|null Option ID or null if extraction fails
     */
    private function getOptionId(Product $product, array|object|int $option, bool $isVariant = false): ?int
    {
        // Extract ID from object
        if (is_object($option)) {
            return $option->id;
        }
        // Extract ID from array
        elseif (is_array($option)) {
            if ($isVariant)
            {
                // For configurable products, extract first element
                return $product->type == ProductTypeCast::CONFIGURABLE ? $option[0] : $option;
            }else{
                // For simple products, look for 'id' key or first element
                return $option['id'] ?? $option[0];
            }
        }
        return $option;
    }


    /**
     * Generate and create product variants for configurable product
     *
     * Orchestrates variant generation process:
     * 1. Generates all possible variant combinations
     * 2. Creates variant product records
     * 3. Attaches filter options to each variant
     *
     * @param Product $product Parent configurable product
     * @param array $productData Original product data
     * @param array $filterOption Filter options for variant generation
     * @return void
     */
    private  function generateProductVariants(Product $product, array $productData, array $filterOption): void
    {
        // Generate and create variants
        if (! empty($filterOption)) {
            // Generate all variant combinations
            $variants = $this->generateVariants($product->sku, $filterOption);

            // Create variant products
            $this->createVariants($product, $productData, $variants);
        }
    }


    /**
     * Generate all possible variant combinations (Cartesian Product)
     *
     * Creates unique SKU and URL for each variant combination based on
     * filter option values.
     *
     * Example: Color [Red, Blue] × Size [S, M] = 4 variants
     *
     * @param string $sku Base SKU for variant generation
     * @param array $filterOptions Array of filter_id => [option_ids]
     * @return array Array of variant data with SKU, URL, and filter_option_ids
     */
    private function generateVariants(string $sku, array $filterOptions): array
    {
        // Get all selected filter option IDs and their corresponding values
        $filterOptionsWithValues = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            // Fetch option values from database
            $options = FilterOption::whereIn('id', $optionIds)
                ->pluck('value', 'id')
                ->map(function ($value) {
                    // Clean up the value by removing 'GSM' and any extra spaces
                    return trim(str_replace('GSM', '', $value));
                })
                ->toArray();
            $filterOptionsWithValues[$filterId] = $options;
        }

        // Generate Cartesian product of all combinations
        $combinations = $this->cartesianProduct($filterOptionsWithValues);

        // Format variants with unique SKUs and URLs
        return array_map(function ($combination) use ($sku) {
            // Create variant identifier from option values
            $optionValues = implode('-', array_values($combination));

            return [
                'sku' => strtoupper("{$sku}-{$optionValues}"),
                'url' => strtolower(str_replace(' ', '-', "{$sku}-{$optionValues}")),
                'filter_option_ids' => array_keys($combination),
            ];
        }, $combinations);
    }


    /**
     * Create variant products for configurable product
     *
     * Creates simple product records for each variant combination.
     * Variants inherit properties from parent product.
     * Prevents duplicate creation by checking existing SKUs.
     *
     * @param Product $product Parent configurable product
     * @param array $productData Original product data for inheritance
     * @param array|null $variants Generated variant combinations
     * @return void
     */
    private function createVariants(Product $product, array $productData, ?array $variants): void
    {
        // Collect all desired SKUs from the generated variants
        $allSkus = collect($variants)->pluck('sku')->toArray();

        // Fetch existing products by SKU to prevent duplicates
        $existingSkus = Product::whereIn('sku', $allSkus)->pluck('sku')->all();

        // Filter only the variants that do NOT exist
        $newVariants = collect($variants)
            ->reject(fn($variant) => in_array($variant['sku'], $existingSkus))
            ->values();

        // Create only new variants
        foreach ($newVariants as $variant) {
            // Create variant product with inherited properties
            $variantProduct = Product::create([
                'parent_id' => $product->id,
                'type' => 'simple',
                'name' => $product->name,
                'sku' => $variant['sku'],
                'url' => $variant['url'],
                'status' => $product->status,
                'description' => $product->description ?? null,
                'short_description' => $product->short_description ?? null,
                'filter_group_id' => $product->filter_group_id,
                'category_id' => $productData['category_id'] ?? null,
                'min_quantity' => $product->min_quantity,
                'max_quantity' => $product->max_quantity,
                'price'        => $product->price,
                'reward_point' => $product->reward_point,
                'tax_code_id' => $product->tax_code_id,
            ]);

            // Attach filter options to the new variant
            $this->attachFilterOptionsToProduct($variantProduct, $variant['filter_option_ids']);
        }
    }


    /**
     * Generate Cartesian Product of filter options
     *
     * Mathematical operation that generates all possible combinations
     * from multiple sets.
     *
     * Example: [A, B] × [1, 2] = [(A,1), (A,2), (B,1), (B,2)]
     *
     * @param array $filterOptionsWithValues Multi-dimensional array of filter options
     * @return array All possible combinations
     */
    private function cartesianProduct(array $filterOptionsWithValues): array
    {
        // Handle empty input
        if (empty($filterOptionsWithValues)) {
            return [[]];
        }

        // Start with empty combination
        $result = [[]];

        // Build combinations iteratively
        foreach ($filterOptionsWithValues as $options) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($options as $optionId => $optionValue) {
                    // Combine existing with new option
                    $newResult[] = $existing + [$optionId => $optionValue];
                }
            }
            $result = $newResult;
        }

        return $result;
    }


    /**
     * Smart variant update - Only add/remove changed variants
     *
     * Algorithm:
     * 1. Compare new filter options with existing variant options
     * 2. If identical, skip processing (optimization)
     * 3. Generate signature for each variant combination
     * 4. Delete variants that no longer exist in new combinations
     * 5. Create only new variants that don't exist
     *
     * This prevents unnecessary deletion/recreation of unchanged variants
     *
     * @param Product $product Parent configurable product
     * @param array $filterOptions New filter options from update
     * @return void
     */
    private function smartUpdateVariants(Product $product, array $filterOptions): void
    {

        // 1. Gather new filter option IDs from the form data
        $newOptionIds = collect($filterOptions)
            ->flatten()
            ->map(fn($v) => (int) $v) // Normalize to integers
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

        // 3. Compare both sets - early return if identical
        if ($newOptionIds === $existingOptionIds) {
            // No change — skip processing for performance
            return;
        }

        // 4. There's a difference — proceed with diffing variant combinations
        // Generate new variant combinations
        $newVariants = $this->generateVariants($product->sku, $filterOptions);

        // Get existing variants with their filter options
        $existingVariants = $product->variants()->with('filterOptions')->get();

        // Create signature map for existing variants (signature => variant)
        // Signature is a sorted string of option IDs (e.g., "5-10-15")
        $existingSignatures = $existingVariants->mapWithKeys(function ($variant) {
            $optionIds = $variant->filterOptions->pluck('id')->sort()->values()->all();
            $signature = implode('-', $optionIds);
            return [$signature => $variant];
        });

        // Create signature map for new variants (signature => variant data)
        $newSignatures = collect($newVariants)->mapWithKeys(function ($variant) {
            $signature = implode('-', collect($variant['filter_option_ids'])->sort()->values()->all());
            return [$signature => $variant];
        });

        // 5. Delete outdated variants (exist in DB but not in new data)
        $toDelete = $existingSignatures->keys()->diff($newSignatures->keys());
        foreach ($toDelete as $signature) {
            $existingSignatures[$signature]->delete();
        }

        // 6. Create only new variants (exist in new data but not in DB)
        $toCreate = $newSignatures->keys()->diff($existingSignatures->keys());
        $variantsToCreate = $toCreate->map(fn($signature) => $newSignatures[$signature])->values()->all();

        if (!empty($variantsToCreate)) {
            $this->createVariants($product, $product->toArray(), $variantsToCreate);
        }

    }


}
