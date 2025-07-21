<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterOption extends Model
{
    /** @use HasFactory<\Database\Factories\FilterOptionFactory> */
    use HasFactory;

    protected $fillable = [
        "value",
        "swatch_value",
    ];


    public function filter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Filter::class,'filter_id','id');
    }


}
