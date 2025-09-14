<?php

namespace Mintreu\LaravelCategory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelCategory\Database\Factories\CategoryFactory;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasPackageModelFactory,HasRecursiveRelationships,InteractsWithMedia;



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
    public function categorized(): MorphToMany
    {
        $targetModel = config('laravel-category.categorized.models')[0] ?? null;


        if (! $targetModel) {
            throw new \RuntimeException(
                "No Category categorized models defined in config/laravel-category.php"
            );
        }



        return $this->morphedByMany(
            $targetModel,  // Target model
            'categorized',               // Morph name (matches your migration)
            'category_mappings',         // Pivot table
            'category_id',               // Foreign key on pivot table pointing to this model
            'categorized_id'            // Morph ID for the Product model
        );
    }




    public function __call($method, $parameters)
    {
        // If method is a known relation, return it dynamically
        if ($relation = $this->resolveDynamicTargetRelation($method)) {
            return $relation;
        }

        // Otherwise, fallback to parent __call
        return parent::__call($method, $parameters);
    }

    protected function resolveDynamicTargetRelation(string $method): ?MorphToMany
    {
        // Config defined target models
        $targets = config('laravel-category.categorized.models', []);
        foreach ($targets as $class) {
            $expected = \Illuminate\Support\Str::plural(strtolower(class_basename($class)));

            if ($method === $expected) {
                return $this->morphedByMany(
                    $class,  // Target model
                    'categorized',               // Morph name (matches your migration)
                    'category_mappings',         // Pivot table
                    'category_id',               // Foreign key on pivot table pointing to this model
                    'categorized_id'
                );
            }
        }

        return null; // no dynamic relation found
    }





}
