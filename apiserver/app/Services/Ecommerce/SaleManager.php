<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\SaleActionTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sale Manager Service
 *
 * Handles sale product indexing and price calculations.
 * Replaces the need for manual sale_price updates by generating
 * sale_product records for all matching products.
 *
 * Also provides condition options for Filament form schemas.
 */
final class SaleManager
{
    protected array $category;

    protected array $filterGroup;

    protected Collection $filters;

    public function __construct()
    {
        $this->category = Category::with('children', 'parent')
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        $this->filterGroup = FilterGroup::all()->pluck('name', 'id')->toArray();

        $this->filters = \App\Models\Ecommerce\Filter::with('options')
            ->has('options')
            ->get();
    }

    public static function make(): static
    {
        return new self;
    }

    // ==================== Filament Form Condition Methods ====================

    /**
     * Get available condition options for Filament form schema
     * Used in SaleResource Create/Edit pages
     */
    public function getCondition(): Collection
    {
        $collection = collect([
            [
                'key' => 'product',
                'label' => trans('catalog-rules.product-filter'),
                'children' => $this->getChildren(),
            ],
        ]);

        $conditions = collect();
        $conditions = $collection->map(function ($item) use ($conditions) {
            return $conditions->merge($item['children']);
        });

        return $conditions[0] ?? collect();
    }

    /**
     * Get children condition options
     */
    private function getChildren(): Collection
    {
        $result = collect([
            [
                'key' => 'product|category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Categories'),
                'options' => $this->category,
            ],
            [
                'key' => 'product|price',
                'type' => 'number',
                'operator' => $this->getOperator('number'),
                'label' => trans('Price'),
                'options' => null,
            ],
        ]);

        return $result->merge($this->getStaticFilters());
    }

    /**
     * Get static filter options from filter groups
     */
    private function getStaticFilters(): array
    {
        $attrBag = [];
        $allFilters = $this->filters;

        foreach ($allFilters as $filter) {
            $key = 'filter|'.$filter->name;
            $attrBag[] = [
                'key' => Str::lower($key),
                'type' => $filter->type,
                'operator' => $this->getOperator(Str::lower($filter->type)),
                'label' => trans(Str::ucfirst($filter->name)),
                'options' => $filter->options->pluck('value', 'id')->toArray(),
            ];
        }

        return $attrBag;
    }

    /**
     * Get operators based on attribute type
     */
    protected function getOperator(string $operatorType): array
    {
        return match ($operatorType) {
            'text' => [
                '=' => 'Contain',
                '!=' => 'Not Contain',
            ],
            'number' => [
                '=' => 'Equal With',
                '>' => 'Greater than',
                '<' => 'Less than',
                '!=' => 'Not Equal',
            ],
            'select', 'multiselect' => [
                '=' => 'Equal With',
                '!=' => 'Not Equal',
            ],
            default => [],
        };
    }

    // ==================== Sale Product Indexing Methods ====================

    /**
     * Reindex all sale products - clean and rebuild
     */
    public function reindexSaleableProducts(): void
    {
        $this->cleanIndex();
    }

    /**
     * Clean existing sale products and rebuild for active sales
     */
    private function cleanIndex(): void
    {
        DB::transaction(function (): void {
            // Clear all expired sale products
            SaleProduct::whereDate('ends_till', '<', now())->delete();

            // Get active sales
            $allSales = Sale::with(['categories', 'products', 'stages', 'levels', 'users'])
                ->where(function ($q): void {
                    $q->where('starts_from', '<=', Carbon::now()->format('Y-m-d'))
                        ->orWhereNull('starts_from');
                })
                ->where(function ($q): void {
                    $q->where('ends_till', '>=', Carbon::now()->format('Y-m-d'))
                        ->orWhereNull('ends_till');
                })
                ->where('status', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Process each sale
            foreach ($allSales as $sale) {
                $this->insertSaleProduct($sale);
            }
        });

        Notification::make()
            ->title('Reindexing Product Sales successfully')
            ->success()
            ->seconds(7)
            ->send();
    }

    /**
     * Insert sale products for a specific sale
     */
    private function insertSaleProduct(Sale $sale, ?Product $product = null): void
    {
        // Clean existing sale products for this sale
        SaleProduct::where('sale_id', $sale->id)->delete();

        $rows = [];
        $productIds = $this->getMatchingProductIds($sale, $product);
        $startsFrom = $sale->starts_from
            ? Carbon::parse($sale->starts_from)->startOfDay()
            : null;
        $endsTill = $sale->ends_till
            ? Carbon::parse($sale->ends_till)->endOfDay()
            : null;
        $targets = $this->getSaleTargets($sale);

        $productQuery = Product::query();
        if (method_exists(Product::class, 'tiers')) {
            $productQuery->with(['tiers' => function ($q): void {
                $q->where('in_stock', true)->orderBy('price');
            }]);
        }
        $productCollection = $productQuery->whereIn('id', $productIds)->get();

        foreach ($productCollection as $product) {
            $calculated = $this->calculate($sale, $product);

            if ($targets->isNotEmpty()) {
                foreach ($targets as $target) {
                    $rows[] = $this->buildSaleProductRow($sale, $product, $target, $calculated, $startsFrom, $endsTill);
                }
            } else {
                $rows[] = $this->buildSaleProductRow($sale, $product, null, $calculated, $startsFrom, $endsTill);
            }
        }

        $this->storeRecord($rows);
    }

    /**
     * Build a single sale product row
     */
    private function buildSaleProductRow(
        Sale $sale,
        Product $product,
        ?object $target,
        int $calculatedPrice,
        ?Carbon $startsFrom,
        ?Carbon $endsTill
    ): array {
        return [
            'starts_from' => $startsFrom,
            'ends_till' => $endsTill,
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target?->getKey(),
            'discount_amount' => $sale->discount_amount,
            'sale_price' => $calculatedPrice,
            'action_type' => $sale->action_type,
            'end_other_rules' => $sale->end_other_rules,
            'sort_order' => $sale->sort_order,
        ];
    }

    /**
     * Store sale product records
     */
    protected function storeRecord(array $data): void
    {
        if (empty($data)) {
            return;
        }

        SaleProduct::insert($data);

        Notification::make()->success()->title('product sale info updated')->send();
    }

    /**
     * Calculate sale price for a product
     */
    public function calculate(Sale $sale, Product $product): int
    {
        $saleActionType = $sale->action_type instanceof SaleActionTypeCast
            ? $sale->action_type->value
            : $sale->action_type;

        $effectivePrice = $this->getEffectivePrice($product);

        if (in_array($saleActionType, [SaleActionTypeCast::TO_PERCENT->value, SaleActionTypeCast::BY_PERCENT->value])) {
            $discountPercentage = $sale->discount_amount;

            return match ($saleActionType) {
                'to_percent' => (int) (($effectivePrice * $discountPercentage) / 100),
                'by_percent' => (int) ($effectivePrice * (1 - ($discountPercentage / 100))),
            };
        }

        $discountAmount = $sale->discount_amount;

        return match ($saleActionType) {
            'to_fixed' => min($discountAmount, $effectivePrice),
            'by_fixed' => max(0, $effectivePrice - $discountAmount),
        };
    }

    /**
     * Get effective price considering tiered pricing
     */
    private function getEffectivePrice(Product $product): int
    {
        return $product->getPrice();
    }

    /**
     * Reindex a single sale
     */
    public function reindexSingleSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $this->insertSaleProduct($sale);
        });

        Notification::make()
            ->title("Sale '{$sale->name}' reindexed successfully")
            ->success()
            ->seconds(5)
            ->send();
    }

    /**
     * Get all product IDs matching a sale's conditions
     */
    private function getMatchingProductIds(Sale $sale, ?Product $product): array
    {
        $bag = $this->prepareBag($sale);
        $allCatProducts = $this->resolveCategoryProducts($bag);
        $allQueryProducts = $this->resolveQueryableProducts($sale, $bag);

        $conditionIds = collect(array_replace(array_keys($allQueryProducts), array_keys($allCatProducts)))
            ->unique()
            ->values()
            ->all();

        $targetProductIds = $sale->products()->pluck('products.id')->toArray();
        $targetCategoryIds = $sale->categories()->pluck('categories.id')->toArray();
        if (! empty($targetCategoryIds)) {
            $categoryProductIds = Product::whereIn('category_id', $targetCategoryIds)->pluck('id')->toArray();
            $targetProductIds = array_unique(array_merge($targetProductIds, $categoryProductIds));
        }

        $hasConditions = ! empty($bag);
        $hasTargets = ! empty($targetProductIds);

        if (! $hasConditions) {
            $ids = $hasTargets
                ? $targetProductIds
                : Product::pluck('id')->toArray();
        } else {
            $ids = $conditionIds;
            if ($hasTargets) {
                $ids = array_values(array_intersect($ids, $targetProductIds));
            }
        }

        if (isset($ids[0]) && $ids[0] === 0) {
            unset($ids[0]);
        }

        return $ids;
    }

    /**
     * Prepare condition bag from sale conditions
     */
    protected function prepareBag(Sale $sale): array
    {
        $conditionList = (array) $sale->conditions;
        $bag = [];

        foreach ($conditionList as $condition) {
            if (! empty($condition['attribute']) && ! empty($condition['operator']) && ! empty($condition['value'])) {
                $chunks = explode('|', $condition['attribute']);

                if (($chunks[1] ?? null) === 'category_id') {
                    if ($condition['operator'] === '=') {
                        $allCats = Category::with('products')->whereIn('id', $condition['value'])->get();
                    } else {
                        $allCats = Category::with('products')->whereNotIn('id', $condition['value'])->get();
                    }

                    foreach ($allCats as $cats) {
                        if ($cats->products) {
                            $bag['cat'][] = $cats->products;
                        }
                    }
                } else {
                    $bag['att'][] = [
                        'column' => strtolower($chunks[1] ?? ''),
                        'operator' => $condition['operator'],
                        'value' => $condition['value'],
                    ];
                }
            }
        }

        return $bag;
    }

    /**
     * Resolve products from category conditions
     */
    private function resolveCategoryProducts(array $bag): array
    {
        $allCatProducts = [];

        if (! empty($bag['cat'])) {
            foreach ($bag['cat'] as $collection) {
                $allCatProducts = array_merge($allCatProducts, $collection->pluck('id')->flip()->toArray());
            }
        }

        return $allCatProducts;
    }

    /**
     * Resolve products from attribute conditions
     */
    private function resolveQueryableProducts(Sale $sale, array $bag): array
    {
        $allQueryProducts = [];
        $availableColumns = Product::first()?->getFillable() ?? [];

        if (! empty($bag['att'])) {
            $query = Product::latest()->where('status', 'published');

            foreach ($bag['att'] as $item) {
                if ($item['column'] === 'price') {
                    $hasTiers = method_exists(Product::class, 'tiers');
                    if ($sale->condition_type) {
                        $query->where(function ($q) use ($item, $hasTiers): void {
                            $q->where('price', $item['operator'], $item['value']);
                            if ($hasTiers) {
                                $q->orWhereHas('tiers', function ($tq) use ($item): void {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
                            }
                        });
                    } else {
                        $query->orWhere(function ($q) use ($item, $hasTiers): void {
                            $q->where('price', $item['operator'], $item['value']);
                            if ($hasTiers) {
                                $q->orWhereHas('tiers', function ($tq) use ($item): void {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
                            }
                        });
                    }
                } elseif (in_array($item['column'], $availableColumns)) {
                    if ($sale->condition_type) {
                        $query->where($item['column'], $item['operator'], $item['value']);
                    } else {
                        $query->orWhere($item['column'], $item['operator'], $item['value']);
                    }
                }
            }

            $allQueryProducts = $query->pluck('id')->unique()->toArray();
        }

        return $allQueryProducts;
    }

    private function getSaleTargets(Sale $sale): Collection
    {
        return collect()
            ->merge($sale->categories)
            ->merge($sale->products)
            ->merge($sale->stages)
            ->merge($sale->levels)
            ->merge($sale->users)
            ->unique(fn ($target) => $target::class.'-'.$target->getKey())
            ->values();
    }
}
