<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\ProductStatusCast;
use App\Models\Address;
use App\Models\Traits\HasSaleAccess;
use App\Models\User;
use Database\Factories\Ecommerce\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasSaleAccess;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'sku',
        'url',
        'status',
        'is_returnable',
        'return_days',
        'filter_group_id',
        'description',
        'short_description',
        'type',
        'parent_id',
        'category_id',
        'price',
        'view_count',
        'seo_meta',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'price' => 'integer',
            'status' => ProductStatusCast::class,
            'is_returnable' => 'boolean',
            'return_days' => 'integer',
        ];
    }

    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class);
    }

    public function filterOptions(): BelongsToMany
    {
        return $this->belongsToMany(FilterOption::class, 'product_filter_options', 'product_id', 'filter_option_id');
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'product_filter_options')
            ->withPivot('filter_option_id');
    }

    public function filterOptionsGrouped()
    {
        return $this->filterOptions()
            ->with('filter')
            ->get()
            ->groupBy('filter_id')
            ->map(function ($options) {
                $filter = $options->first()->filter;

                return [
                    'filter' => $filter,
                    'options' => $options,
                ];
            });
    }

    public function scopeWithFilters(Builder $query, array $filterIds): Builder
    {
        return $query->whereHas('filters', function ($q) use ($filterIds) {
            $q->whereIn('filters.id', $filterIds);
        });
    }

    /**
     * Limit query to products that are published/visible for customer flows.
     *
     * Acts as a single place to enforce storefront visibility checks so the
     * cart, catalog, and other flows don't need to duplicate status logic.
     */
    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('status', ProductStatusCast::PUBLISHED->value);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function siblingsAndSelf()
    {
        return Product::where('category_id', $this->category_id)
            ->whereNull('parent_id')
            ->where('id', '!=', $this->parent_id);
    }

    public function siblings()
    {
        return $this->siblingsAndSelf()->where('id', '!=', $this->id);
    }

    /**
     * Register media collections for Spatie Media Library
     * Collection names match old_project for compatibility
     */
//    public function registerMediaCollections(): void
//    {
//        $this->addMediaCollection('displayImage')
//            ->singleFile()
//            ->useFallbackUrl('/images/product-placeholder.png');
//
//        $this->addMediaCollection('bannerImage');
//    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            ->useDisk('public')
            ->singleFile()
            ->useFallbackUrl('/images/product-placeholder.png');

        $this->addMediaCollection('bannerImage')
            ->useDisk('public');
    }



    /**
     * Register media conversions for responsive images
     * Generate thumbnail and responsive srcsets for fast loading
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        // Only apply conversions to images in our collections
        if ($media && in_array($media->collection_name, ['displayImage', 'bannerImage'])) {
            $this->addMediaConversion('responsive')
                ->withResponsiveImages()
                ->nonQueued();
        }
    }

    public function cartedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'carts')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Stock Management
     * All stock records for this product across different warehouses/locations
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Available stock records where inventory is in stock
     * Returns stock records with in_stock = true
     */
    public function availableStocks(): HasMany
    {
        return $this->stocks()->where('in_stock', true)->orderBy('priority');
    }

    public function preferredWarehouseAddress(): ?Address
    {
        return $this->availableStocks()
            ->with('address')
            ->first()
            ?->address;
    }

    /**
     * Get minimum available stock quantity
     * Returns the lesser of available stock or requested count
     *
     * Note: This method executes a query. For better performance in loops,
     * use Product::withStockInfo()->get() and access $product->total_stock instead
     */
    public function minStock(int $count): int
    {
        // Try to use preloaded aggregate first to avoid N+1
        $availableMinStock = $this->stocks_sum_in_stock_quantity ?? $this->availableStocks()->sum('in_stock_quantity');

        return min($availableMinStock ?? 0, $count);
    }

    /**
     * Total stock count across all warehouses
     *
     * Note: This method executes a query. For better performance in loops,
     * use Product::withStockInfo()->get() and access $product->total_stock instead
     */
    public function totalStock(): int
    {
        // Try to use preloaded aggregate first to avoid N+1
        return (int) ($this->stocks_sum_in_stock_quantity ?? $this->stocks()->sum('in_stock_quantity'));
    }

    /**
     * Query Optimization Scopes
     * Prevent N+1 queries when loading products with stock information
     */

    /**
     * Eager load stock aggregates (sums and counts)
     * Prevents N+1 queries when displaying product listings with stock info
     *
     * Usage: Product::withStockInfo()->get()
     * Access: $product->stocks_sum_in_stock_quantity, $product->stocks_count
     */
    public function scopeWithStockInfo(Builder $query): Builder
    {
        return $query->withSum('stocks', 'in_stock_quantity')
            ->withCount(['stocks', 'availableStocks']);
    }

    /**
     * Eager load all stock records with their addresses
     * Use when you need full stock details
     *
     * Usage: Product::withStocks()->get()
     */
    public function scopeWithStocks(Builder $query): Builder
    {
        return $query->with(['stocks' => function ($q) {
            $q->with('address');
        }]);
    }

    /**
     * Eager load only available stock records
     * More efficient than loading all stocks when you only need available ones
     *
     * Usage: Product::withAvailableStocks()->get()
     */
    public function scopeWithAvailableStocks(Builder $query): Builder
    {
        return $query->with(['availableStocks' => function ($q) {
            $q->with('address');
        }]);
    }

    /**
     * Filter products that have low stock
     * Useful for inventory management dashboards
     *
     * Usage: Product::lowStock()->get()
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereRaw('in_stock_quantity <= low_stock_threshold')
                ->where('notify_on_low_stock', true);
        });
    }

    /**
     * Filter products that are out of stock
     *
     * Usage: Product::outOfStock()->get()
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('availableStocks');
    }

    /**
     * Filter products that are in stock
     *
     * Usage: Product::inStock()->get()
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('availableStocks');
    }

    /**
     * Optimized getter for total stock when aggregate is preloaded
     * Falls back to query if not preloaded
     */
    public function getTotalStockAttribute(): int
    {
        return (int) ($this->stocks_sum_in_stock_quantity ?? $this->totalStock());
    }

    /**
     * Optimized getter for available stock count when aggregate is preloaded
     * Falls back to query if not preloaded
     */
    public function getAvailableStockCountAttribute(): int
    {
        return (int) ($this->available_stocks_count ?? $this->availableStocks()->count());
    }
}
