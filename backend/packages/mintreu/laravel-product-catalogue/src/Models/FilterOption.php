<?php

namespace Mintreu\LaravelProductCatalogue\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\FilterOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterOption extends Model
{
    /** @use HasFactory<FilterOptionFactory> */
    use HasFactory;

    protected $fillable = [
        "value",
        "swatch_value",
    ];


    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class,'filter_id','id');
    }


}
