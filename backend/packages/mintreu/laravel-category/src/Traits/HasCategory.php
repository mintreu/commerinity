<?php

namespace Mintreu\LaravelCategory\Traits;

use Mintreu\LaravelCategory\Models\Category;

trait HasCategory
{

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

}
