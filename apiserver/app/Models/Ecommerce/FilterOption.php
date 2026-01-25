<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FilterOption extends Model
{
    /** @use HasFactory<FilterOptionFactory> */
    use HasFactory;

    protected $fillable = ['filter_id', 'value', 'swatch_value'];

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class,'filter_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_filter_options', 'filter_option_id', 'product_id');
    }
}
