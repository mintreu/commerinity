<?php

namespace App\Models;

use App\Casts\ModelStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Traits\Cart\HasCartable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory,HasRecursiveRelationships,InteractsWithMedia,HasCartable;


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
        'status' => ModelStatusCast::class,
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
            //->useFallbackUrl(asset('images/placeholder/user_placeholder.png'));
            ->useFallbackUrl('https://i.pravatar.cc/300');
        $this->addMediaCollection('bannerImage')
            //->useFallbackUrl(asset('images/placeholder/user_placeholder.png'));
            ->useFallbackUrl('https://placehold.co/400x1200');
    }


    public function categories()
    {
        return $this->morphToMany(
            Category::class, // Target model
            'categorized',               // Morph name
            'category_mappings',         // Pivot table
            'categorized_id',            // Foreign key on pivot pointing to this model
            'category_id'                // Foreign key for Category
        );
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
//    public function variants(): HasMany
//    {
//        return $this->hasMany(Product::class, 'parent_id');
//    }


    public function variants():HasMany
    {
        return $this->hasMany(static::class, 'parent_id','id');
    }


    public function tiers(): HasMany
    {
        return $this->hasMany(ProductTier::class,'product_id','id');
    }



}
