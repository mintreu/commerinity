<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory,HasRecursiveRelationships,InteractsWithMedia;



    protected $fillable = [
        'name',
        'url',
        'status',
        'is_visible_on_front',
        'view_count',
        'order',
        'meta_data',
        'desc',
        'parent_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_visible_on_front' => 'boolean',
        'view_count' => 'integer',
        'order' => 'integer',
        'meta_data' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'url';
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

    public function mappings()
    {
        return $this->hasMany('category_mapping','category_id','id');
    }
    public function products()
    {
        return $this->morphedByMany(
            Product::class,  // Target model
            'categorized',               // Morph name (matches your migration)
            'category_mappings',         // Pivot table
            'category_id',               // Foreign key on pivot table pointing to this model
            'categorized_id'            // Morph ID for the Product model
        );
    }

}
