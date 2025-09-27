<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductEngagement extends Model
{
    /** @use HasFactory<\Database\Factories\ProductEngagementFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'authorable_id',
        'authorable_type',
        'rating',
        'review',
        'helpful_votes',
    ];

    protected $casts = [
        'rating' => 'integer',
        'helpful_votes' => 'integer',
    ];

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }







}
