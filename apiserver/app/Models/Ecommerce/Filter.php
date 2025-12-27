<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Database\Factories\FilterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    /** @use HasFactory<FilterFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function options(): HasMany
    {
        return $this->hasMany(FilterOption::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(FilterGroup::class, 'filter_group_mappings');
    }
}
