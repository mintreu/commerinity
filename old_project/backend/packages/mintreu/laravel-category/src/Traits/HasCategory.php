<?php

namespace Mintreu\LaravelCategory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelCategory\Models\Category;

trait HasCategory
{

    public function categories(): MorphToMany
    {
        return $this->morphToMany(
            Category::class, // Target model
            'categorized',               // Morph name
            'category_mappings',         // Pivot table
            'categorized_id',            // Foreign key on pivot pointing to this model
            'category_id'                // Foreign key for Category
        )->withPivot('base_category');
    }



    /**
     * Direct category relation for models with category_id.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Helper to get "primary" category: direct first, fallback to morph.
     */
    public function primaryCategory(): null|Category|Model
    {
        return $this->category ?? $this->categories()->first();
    }



}
