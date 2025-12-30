<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\SaleActionTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sale Manager Service
 *
 * Handles sale product indexing and price calculations.
 * Replaces the need for manual sale_price updates by generating
 * sale_product records for all matching products.
 */
final class SaleManager
{
    protected array $category;

    public function __construct()
    {
        $this->category = Category::with('children', 'parent')
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function make(): static
    {
        return new self;
    }

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
            $allSales = Sale::with('targets')
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
        $startsFrom = $sale->starts_from ? Carbon::createFromTimeString($sale->starts_from.' 00:00:01') : null;
        $endsTill = $sale->ends_till ? Carbon::createFromTimeString($sale->ends_till.' 23:59:59') : null;

        $productCollection = Product::with(['tiers' => function ($q): void {
            $q->where('in_stock', true)->orderBy('price');
        }])->whereIn('id', $productIds)->get();

        foreach ($productCollection as $product) {
            $calculated = $this->calculate($sale, $product);

            if ($sale->targets->count()) {
                foreach ($sale->targets as $target) {
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
        if ($product->relationLoaded('tiers')) {
            $cheapestTier = $product->tiers->first();

            return $cheapestTier?->price ?? $product->price;
        }

        $cheapestTier = $product->tiers()
            ->where('in_stock', true)
            ->orderBy('price')
            ->first();

        return $cheapestTier?->price ?? $product->price;
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

        $uniques = collect(array_replace(array_keys($allQueryProducts), array_keys($allCatProducts)))
            ->unique();
        $ids = $uniques->values()->all();

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
                    if ($sale->condition_type) {
                        $query->where(function ($q) use ($item): void {
                            $q->where('price', $item['operator'], $item['value'])
                                ->orWhereHas('tiers', function ($tq) use ($item): void {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
                        });
                    } else {
                        $query->orWhere(function ($q) use ($item): void {
                            $q->where('price', $item['operator'], $item['value'])
                                ->orWhereHas('tiers', function ($tq) use ($item): void {
                                    $tq->where('in_stock', true)
                                        ->where('price', $item['operator'], $item['value']);
                                });
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
}
