<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Centralized product query builder for storefront-style product reads.
 *
 * This keeps catalog, wishlist, deals, and similar endpoints aligned on:
 * - visibility (purchasable)
 * - top-level product selection
 * - eager-load strategy
 * - common request filters + sorting
 */
final class ProductQueryService
{
    /**
     * Base storefront products (published + non-variant parent rows).
     */
    public function storefrontBaseQuery(): Builder
    {
        return \App\Models\Ecommerce\Product::query()
            ->purchasable()
            ->whereNull('parent_id');
    }

    /**
     * Standard eager loads for product cards/lists.
     */
    public function applyStorefrontEagerLoads(Builder $query): Builder
    {
        return $query
            ->with(['category', 'media'])
            ->withStockInfo();
    }

    /**
     * Load warehouse/address-ready stock entries for display.
     */
    public function withAvailableStocks(Builder $query): Builder
    {
        return $query->with([
            'availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at'),
        ]);
    }

    /**
     * Apply the common request filters used in catalog/product listings.
     */
    public function applyCatalogRequestFilters(Builder $query, Request $request, bool $includeCategory = true): Builder
    {
        $query->search($request->input('search'));

        if ($includeCategory) {
            $query->byCategory($request->input('category'));
        }

        $query->byPrice(
            $request->input('min_price') ? (int) $request->input('min_price') * 100 : null,
            $request->input('max_price') ? (int) $request->input('max_price') * 100 : null
        );

        $minRating = $request->input('min_rating');
        if (is_numeric($minRating) && (float) $minRating > 0) {
            $query->whereExists(function ($ratingQuery) use ($minRating): void {
                $ratingQuery->selectRaw('1')
                    ->from('product_engagements')
                    ->whereColumn('product_engagements.product_id', 'products.id')
                    ->whereNull('product_engagements.parent_id')
                    ->whereNotNull('product_engagements.rating')
                    ->groupBy('product_engagements.product_id')
                    ->havingRaw('AVG(product_engagements.rating) >= ?', [(float) $minRating]);
            });
        }

        if ($request->boolean('has_bv')) {
            $query->where('bv', '>', 0);
        }

        if ($request->boolean('has_pv')) {
            $query->where('pv', '>', 0);
        }

        return $query;
    }

    /**
     * Apply standard product sorting.
     */
    public function applySort(Builder $query, ?string $sort): Builder
    {
        return $query->sort($sort);
    }

    /**
     * Standard product relation query used from wishlist and nested relations.
     */
    public function applyWishlistProductRelation(Builder $query): Builder
    {
        return $query
            ->with([
                'media' => fn ($mq) => $mq->where('collection_name', 'displayImage'),
                'category',
            ])
            ->withStockInfo();
    }
}
