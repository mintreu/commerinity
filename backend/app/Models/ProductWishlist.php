<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductWishlist extends Model
{
    /** @use HasFactory<\Database\Factories\ProductWishlistFactory> */
    use HasFactory;


    protected $fillable = [
        'product_id',
        'authorable_id',
        'authorable_type',
    ];


    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id');
    }



}
