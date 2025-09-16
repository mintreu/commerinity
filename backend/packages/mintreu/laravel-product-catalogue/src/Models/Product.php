<?php

namespace Mintreu\LaravelProductCatalogue\Models;


use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelCategory\Traits\HasCategory;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Database\Factories\ProductFactory;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasPackageModelFactory,HasRecursiveRelationships,InteractsWithMedia,HasCategory;


    protected $fillable = [
        'name',
        'parent_id',
        'sku',
        'url',
        'type',
        'filter_group_id',
//        'category_id',
        'tenant_id',
        'tenant_type',
        'description',
        'short_description',
        'price',
        'reward_point',
        'min_quantity',
        'max_quantity',
//        'wholesale_unit_quantity',
        'is_returnable',
        'width',
        'height',
        'length',
        'weight',
        'status',
        'status_feedback',
        'view_count',
        'meta_data',
    ];

    protected $casts = [
        'price' => LaravelMoneyCast::class,
        'reward_point' => 'float',
        'is_returnable' => 'boolean',
        'view_count' => 'integer',
        'meta_data' => 'array',
        'type' => ProductTypeCast::class,
        'status' => PublishableStatusCast::class,
        'min_quantity' => 'integer',
        'wholesale_unit_quantity' => 'integer'

    ];

    /**
     * Get the tenant (vendor, store, etc.) for this product.
     */
    public function tenant(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            //->useFallbackUrl('https://placehold.co/600x400?text=Display\nImage')
        ;
        $this->addMediaCollection('bannerImage')
            //->useFallbackUrl('https://placehold.co/600x400?text=Banner\nImage')
        ;
    }




    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class,'filter_group_id','id');
    }

    public function filterOptions(): BelongsToMany
    {
        return $this->belongsToMany(FilterOption::class, 'product_filter_options', 'product_id', 'filter_option_id');
    }

    /**
     * Get filters relationship for this product
     */
    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'product_filter_options')
            ->withPivot('filter_option_id');
    }

    /**
     * Get the variants of this product
     */
    public function variants():HasMany
    {
        return $this->hasMany(static::class, 'parent_id','id');
    }


    public function tiers(): HasMany
    {
        return $this->hasMany(ProductTier::class,'product_id','id');
    }







    /**
     * Relation: Sales for this product
     */
    public function sales(): HasMany
    {
        if (config('laravel-product-catalogue.sales.enabled')) {
            return $this->hasMany(config('laravel-product-catalogue.sales.related_model'), 'product_id');
        }

        // Safe fallback: empty relation
        return $this->hasMany(\Illuminate\Database\Eloquent\Model::class, 'id')
            ->whereRaw('1 = 0');
    }

    /**
     * Scope: Eager load sales
     *
     * Usage:
     * Product::sales()->get();
     */
    public function scopeSales(Builder $query): Builder
    {
        if (!config('laravel-product-catalogue.sales.enabled')) {
            return $query;
        }

        return $query->with('sales');
    }

    /**
     * Scope: Only products that have sales
     *
     * Usage:
     * Product::hasSales()->get();
     */
    public function scopeHasSales(Builder $query): Builder
    {
        if (!config('laravel-product-catalogue.sales.enabled')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('sales');
    }


}
