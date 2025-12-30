<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Database\Factories\Ecommerce\CategoryFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasRecursiveRelationships, InteractsWithMedia;

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            if (empty($category->url)) {
                $category->url = $category->slug;
            }
        });
    }

    protected $fillable = [
        'name',
        'url',
        'parent_id',
        'status',
        'view_count',
        'order',
        'desc',
        'meta_data',
        'banners',
        'seo_meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'meta_data' => AsArrayObject::class,
            'seo_meta' => 'array',
            'banners' => AsArrayObject::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile();

        $this->addMediaCollection('banner')
            ->singleFile();
    }

    public function getRouteKeyName(): string
    {
        return 'url';
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
