<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Database\Factories\Ecommerce\FilterGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterGroup extends Model
{
    /** @use HasFactory<FilterGroupFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'filter_group_mappings');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
