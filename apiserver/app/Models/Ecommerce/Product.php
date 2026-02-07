<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\GstTaxCast;
use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
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
        'type', // product types
        'sku',
        'url',  // this is used all area for routke key.. no need uuid or id for product.. in apiserver we can use internally the id column but for display and navigation api usage use url
        'status',
        'is_returnable',
        'return_days',  // the minimum set days (default: 0) after that bv, pv, commissions etc will be distributed as successfully completed sales
        'filter_group_id',
        'description',
        'short_description',
        'parent_id',
        'category_id',
        'base_price', // mrp
        'price',  // default current offering sale price without applying any sales
        'hsn',
        'gst_tax_type',
        'bv',  // for members and promoters type users only
        'pv',  // for members and promoters type users only
        'reward_points',  // for who purchase wallet reward points count increasing on purchase
        'min_quantity',  // for cart
        'max_quantity',  // for cart
        'wholesale_unit_quantity',  // for distributor case .. not applicable for users in nuxt client side
        'weight_grams',
        'length_cm',
        'width_cm',
        'height_cm',
        'is_commissionable',  // for distributor type user to get commission from their own originator user teams
        'commission_rate',  // legacy name: distributor_percentage
        'distributor_percentage',
        'view_count',
        'seo_meta', // this can be rename as only meta .. json column
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'price' => 'integer',
            'hsn' => 'string',
            'gst_tax_type' => GstTaxCast::class,
            'status' => ProductStatusCast::class,
            'is_returnable' => 'boolean',
            'return_days' => 'integer',
            'type' => ProductTypeCast::class,
            'bv' => 'integer',
            'pv' => 'integer',
            'reward_points' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'wholesale_unit_quantity' => 'integer',
            'weight_grams' => 'integer',
            'length_cm' => 'integer',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
            'is_commissionable' => 'boolean',
            'commission_rate' => 'decimal:2',
            'distributor_percentage' => 'decimal:2',
        ];
    }

    /**
     * Alias for commission_rate to keep naming clear for distributors.
     */
    public function getDistributorPercentageAttribute(): ?float
    {
        return $this->commission_rate;
    }

    public function setDistributorPercentageAttribute(?float $value): void
    {
        $this->attributes['commission_rate'] = $value;
    }

    protected static function booted(): void
    {
        parent::booted();
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
     * Scope: Search by name, sku, short description
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: Filter by Category (and descendants)
     */
    public function scopeByCategory(Builder $query, ?string $categoryUrl): Builder
    {
        if (empty($categoryUrl)) {
            return $query;
        }

        return $query->whereHas('category', function ($q) use ($categoryUrl) {
            // Find category by URL, then get ID and children
            $category = Category::where('url', $categoryUrl)->first();
            if ($category) {
                // Simplified: Just this category for now.
                // Ideally use a closure table or nested set for strict hierarchy
                $q->where('id', $category->id)
                    ->orWhere('parent_id', $category->id);
            } else {
                $q->where('url', $categoryUrl);
            }
        });
    }

    /**
     * Scope: Filter by Price Range via ProductStock
     */
    public function scopeByPrice(Builder $query, ?int $min, ?int $max): Builder
    {
        if (is_null($min) && is_null($max)) {
            return $query;
        }

        if (! is_null($min)) {
            $query->where('price', '>=', $min);
        }

        if (! is_null($max)) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope: Sort products
     */
    public function scopeSort(Builder $query, ?string $sortBy): Builder
    {
        return match ($sortBy) {
            // Price sorting is heavy - requires join.
            // For now, doing simple sorts. Price sort requires subquery/join refactor.
            'newest' => $query->orderBy('created_at', 'desc'),
            'popularity' => $query->orderBy('view_count', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
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

    /**
     * Get the correct price for this product
     *
     * @param  Address|string|null  $delivery  Delivery context (ignored for price, kept for compatibility)
     * @return int Price in paise
     */
    public function getPrice(Address|string|null $delivery = null): int
    {
        return $this->price;
    }

    /**
     * Get formatted price string
     */
    public function getFormattedPrice(): string
    {
        return \App\Services\MoneyService::format($this->getPrice());
    }

    /**
     * Get price range for products with multiple stock entries
     * Returns string like "₹199 - ₹299" or "₹250" for single price
     */
    public function getPriceRange(): string
    {
        // For now, return single price since we are decoupling from stock pricing
        return $this->getFormattedPrice();
    }

    /**
     * Check if product has sale price
     * Returns sale price in paise if available, null otherwise
     */
    public function getSalePrice(): ?int
    {
        $originalPrice = $this->getPrice();
        $saleInfo = $this->getActiveSaleInfo();

        if (is_array($saleInfo) && $originalPrice > 0) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                return $salePrice;
            }
        }

        return null;
    }

    /**
     * Get current display price (sale price if available, otherwise regular price)
     */
    public function getDisplayPrice(): int
    {
        return $this->getSalePrice() ?? $this->getPrice();
    }

    /**
     * Get discount percentage if sale is active
     */
    public function getDiscountPercent(): ?float
    {
        $salePrice = $this->getSalePrice();
        $originalPrice = $this->getPrice();

        if ($salePrice && $salePrice < $originalPrice) {
            $priceService = app(\App\Services\Ecommerce\PriceCalculationService::class);

            return $priceService->calculateDiscountPercent($originalPrice, $salePrice);
        }

        return null;
    }

    /**
     * Get active sale info (simplified - to be implemented with actual sale system)
     */
    private function getActiveSaleInfo(): ?array
    {
        // Check if sale info was eager loaded
        if ($this->relationLoaded('activeSaleInfo')) {
            return $this->activeSaleInfo;
        }

        // TODO: Implement actual sale system integration
        return null;
    }

    /**
     * Calculate sale price from sale info (simplified)
     */
    private function calculateSalePrice(int $originalPrice, array $saleInfo): ?int
    {
        if (isset($saleInfo['sale_product'])) {
            return $saleInfo['sale_product']->getFinalPrice($originalPrice);
        }

        if (isset($saleInfo['sale'])) {
            return $saleInfo['sale']->calculatePrice($originalPrice);
        }

        return null;
    }
}
