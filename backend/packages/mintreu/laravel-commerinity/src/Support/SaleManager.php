<?php

namespace Mintreu\LaravelCommerinity\Support;

use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelCommerinity\Models\SaleProduct;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Models\Filter;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class SaleManager
{
    protected array $category;
    protected array|\Illuminate\Database\Eloquent\Collection $filter;
    protected bool $showOperators = false;
    protected $filterGroup;

    public function __construct()
    {
        $this->category = Category::with('children', 'parent')->where('status', true)->pluck('name', 'id')->toArray();
        $this->filterGroup = FilterGroup::all()->pluck('name', 'id')->toArray();
        $this->filter = Filter::with('options')->has('options')->get();
    }

    public static function make(): static
    {
        return new static();
    }

    protected function getOperator(string $operator_type): array
    {
        return match ($operator_type) {
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

    public function getCondition(): Collection
    {
        $collection = collect([[
            'key' => 'product',
            'label' => trans('catalog-rules.product-filter'),
            'children' => $this->getChildren(),
        ]]);
        $conditions = collect();
        $conditions = $collection->map(function ($item, $key) use ($conditions) {
            return $conditions->merge($item['children']);
        });

        return $conditions[0];
    }

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

        return $result->merge($this->getStaticfilters());
    }

    private function getStaticfilters(): array
    {
        $attrBag = [];
        $allFilters = $this->filter;
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

    public function reindexSaleableProducts(): void
    {
        $this->cleanIndex();
    }

    // FIXED: Better cleanup approach
    private function cleanIndex(): void
    {
        DB::transaction(function () {
            // Step 1: Clear all expired sale products globally
            SaleProduct::whereDate('ends_till', '<', now())->delete();

            // Step 2: Get active sales
            $allSales = Sale::with('targets')
                ->where(function ($q) {
                    $q->where('starts_from', '<=', Carbon::now()->format('Y-m-d'))
                        ->orWhereNull('starts_from');
                })
                ->where(function ($q) {
                    $q->where('ends_till', '>=', Carbon::now()->format('Y-m-d'))
                        ->orWhereNull('ends_till');
                })
                ->where('status', 1)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Step 3: Process each sale individually
            foreach ($allSales as $sale) {
                $this->insertSaleProduct($sale);
            }
        });

        // Send notification
        Notification::make()
            ->title('Reindexing Product Sales successfully')
            ->success()
            ->seconds(7)
            ->send();
    }

    // FIXED: Clean existing sale products before refilling
    private function insertSaleProduct(Sale $sale, $product = null): void
    {
        // STEP 1: Clean existing sale products for this specific sale
        $this->cleanExistingSaleProducts($sale);

        // STEP 2: Generate new sale products
        $rows = [];
        $productIds = $this->getMatchingProductIds($sale, $product);
        $startsFrom = $sale->starts_from ? Carbon::createFromTimeString($sale->starts_from.' 00:00:01') : null;
        $endsTill = $sale->ends_till ? Carbon::createFromTimeString($sale->ends_till.' 23:59:59') : null;

        // FIXED: Eager load tiers to prevent N+1 queries
        $productCollection = Product::with(['tiers' => function ($q) {
            $q->where('in_stock', true)->orderBy('price');
        }])->whereIn('id', $productIds)->get();

        foreach ($productCollection as $product) {
            $calculated = $this->calculate($sale, $product);

            if ($sale->targets->count()) {
                // Prepare Group Sale
                foreach ($sale->targets as $target) {
                    $rows[] = [
                        'starts_from'      => $startsFrom,
                        'ends_till'        => $endsTill,
                        'sale_id'          => $sale->id,
                        'product_id'       => $product->id,
                        'target_type'      => get_class($target),
                        'target_id'        => $target->getKey(),
                        'discount_amount'  => $sale->discount_amount,
                        'sale_price'       => $calculated,
                        'action_type'      => $sale->action_type,
                        'end_other_rules'  => $sale->end_other_rules,
                        'sort_order'       => $sale->sort_order,
                    ];
                }
            } else {
                // Prepare Normal Sale
                $rows[] = [
                    'starts_from'      => $startsFrom,
                    'ends_till'        => $endsTill,
                    'sale_id'          => $sale->id,
                    'product_id'       => $product->id,
                    'target_type'      => null,
                    'target_id'        => null,
                    'discount_amount'  => $sale->discount_amount,
                    'sale_price'       => $calculated,
                    'action_type'      => $sale->action_type,
                    'end_other_rules'  => $sale->end_other_rules,
                    'sort_order'       => $sale->sort_order,
                ];
            }
        }

        // STEP 3: Insert new sale products
        $this->storeRecord($rows);
    }

    // NEW: Clean existing sale products for a specific sale
    private function cleanExistingSaleProducts(Sale $sale): void
    {
        SaleProduct::where('sale_id', $sale->id)->delete();
    }

    protected function storeRecord(array $data)
    {
        if (empty($data)) {
            return;
        }

        // Since we cleaned existing products, we can use insert instead of upsert for better performance
        SaleProduct::insert($data);

        Notification::make()->success()->title('product sale info updated')->send();
    }

    // FIXED: Now uses tier-aware pricing
    // FIXED: Proper calculation with correct discount handling
    public function calculate(Sale $sale, Product $product)
    {
        $saleActionType = $sale->action_type->value;

        // FIXED: Use effective price (tier or product price) instead of just product price
        $effectivePrice = $this->getEffectivePrice($product);

        // Handle discount based on action type
        if (in_array($saleActionType, [SaleActionTypeCast::TO_PERCENT->value, SaleActionTypeCast::BY_PERCENT->value])) {
            // For percentage: discount_amount is stored as percentage (e.g., 10 for 10%)
            $discountPercentage = $sale->discount_amount; // 10 for 10%

            return match ($saleActionType) {
                'to_percent' => intval(($effectivePrice * $discountPercentage) / 100), // ₹450 × 10% = ₹45
                'by_percent' => intval($effectivePrice * (1 - ($discountPercentage / 100))), // ₹450 × (1 - 0.10) = ₹405
            };
        } else {
            // For fixed: discount_amount is stored in paisa (e.g., 1000 for ₹10.00)
            $discountAmount = $sale->discount_amount; // Already in paisa

            return match ($saleActionType) {
                'to_fixed' => min($discountAmount, $effectivePrice), // min(₹0.10, ₹450) = ₹0.10
                'by_fixed' => max(0, $effectivePrice - $discountAmount), // ₹450 - ₹0.10 = ₹449.90
            };
        }
    }


    // NEW: Get effective price considering tiers (matches your ProductDisplayController logic)
    private function getEffectivePrice(Product $product): int
    {
        // Check if product has loaded tiers (from eager loading)
        if ($product->relationLoaded('tiers')) {
            $cheapestTier = $product->tiers->first(); // Already sorted by price ASC
            return $cheapestTier ? $cheapestTier->price : $product->price;
        }

        // Fallback: Query if not eager loaded
        $cheapestTier = $product->tiers()
            ->where('in_stock', true)
            ->orderBy('price')
            ->first();

        return $cheapestTier ? $cheapestTier->price : $product->price;
    }

    // NEW: Method to reindex a single sale (useful for when updating individual sales)
    public function reindexSingleSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $this->insertSaleProduct($sale);
        });

        Notification::make()
            ->title("Sale '{$sale->name}' reindexed successfully")
            ->success()
            ->seconds(5)
            ->send();
    }

    private function getMatchingProductIds(Sale $sale, mixed $product): array
    {
        // Prepare Conditions
        $bag = $this->prepareBag($sale);
        // Fetch Matched Products
        $allCatProducts = $this->resolveCategoryProducts($sale, $bag);
        $allQueryProducts = $this->resolveQueryableProducts($sale, $bag);

        // Unique Product IDs
        $uniques = collect(array_replace(array_keys($allQueryProducts), array_keys($allCatProducts)))->unique();
        $ids = $uniques->values()->all();

        if (isset($ids[0])) {
            if ($ids[0] === 0) {
                unset($ids[0]);
            }
        }

        return $ids;
    }

    protected function prepareBag(Sale $sale): array
    {
        $conditionList = (array) $sale->conditions;
        $bag = [];
        // Lets Check
        foreach ($conditionList as $condition) {
            // First Check Condition Format & Value
            if (! empty($condition['attribute']) && ! empty($condition['operator']) && ! empty($condition['value'])) {
                $key = $condition['attribute'];
                $chunks = explode('|', $condition['attribute']);

                if ($chunks[1] === 'category_id' || $chunks[1] === 'filter_group_id') {
                    if ($chunks[1] === 'category_id') {
                        if ($condition['operator'] === '=') {
                            $allCats = Category::with('products')->whereIn('id', $condition['value'])->get();
                        } else {
                            $allCats = Category::with('products')->whereNotIn('id', $condition['value'])->get();
                        }

                        // Fill Array With All Categories Products
                        foreach ($allCats as $cats) {
                            if ($cats->products) {
                                $bag['cat'][] = $cats->products;
                            }
                        }
                    }
                } else {
                    $bag['att'][] = [
                        'column' => strtolower($chunks[1]),
                        'operator' => $condition['operator'],
                        'value' => $condition['value'],
                    ];
                }
            }
        }

        return $bag;
    }

    private function resolveCategoryProducts(Sale $sale, array $bag): array
    {
        $allCatProducts = [];
        if (! empty($bag['cat'])) {
            foreach ($bag['cat'] as $collection) {
                $allCatProducts = array_merge($allCatProducts, $collection->pluck('id')->flip()->toArray());
            }
        }

        return $allCatProducts;
    }

    // ENHANCED: Better performance and tier handling
    private function resolveQueryableProducts(Sale $sale, array $bag): array
    {
        $allQueryProducts = [];
        $availableColumns = Product::first()?->getFillable() ?? [];

        if (! empty($bag['att'])) {
            $query = Product::latest()->where('status', PublishableStatusCast::PUBLISHED);

            foreach ($bag['att'] as $item) {
                // Handle "price" column separately - ENHANCED with better tier logic
                if ($item['column'] === 'price') {
                    if ($sale->condition_type) {
                        // AND logic
                        $query->where(function ($q) use ($item) {
                            $q->where('price', $item['operator'], $item['value'])
                                ->orWhereHas('tiers', function ($tq) use ($item) {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
                        });
                    } else {
                        // OR logic
                        $query->orWhere(function ($q) use ($item) {
                            $q->where('price', $item['operator'], $item['value'])
                                ->orWhereHas('tiers', function ($tq) use ($item) {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
                        });
                    }
                }
                // Handle other product columns
                elseif (in_array($item['column'], $availableColumns)) {
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
}
