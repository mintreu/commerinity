<?php

namespace App\Models;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory,HasRecursiveRelationships,InteractsWithMedia;


    protected $fillable = [
        'name',
        'parent_id',
        'sku',
        'url',
        'type',
        'filter_group_id',
        'category_id',
        'tenant_id',
        'tenant_type',
        'description',
        'short_description',
        'price',
        'reward_point',
        'is_returnable',
        'status',
        'status_feedback',
        'view_count',
        'meta_data',
    ];

    protected $casts = [
        'price' => 'integer',
        'reward_point' => 'float',
        'is_returnable' => 'boolean',
        'view_count' => 'integer',
        'meta_data' => 'array',
        'type' => ProductTypeCast::class,
        'status' => ProductStatusCast::class
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

    public function filter_group()
    {
        return $this->belongsTo(FilterGroup::class,'filter_group_id','id');
    }



}
