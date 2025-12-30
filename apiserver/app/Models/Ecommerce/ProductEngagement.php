<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ProductEngagement - Reviews, Ratings, and Comments
 *
 * Supports:
 * - Product reviews with 1-5 star ratings
 * - Threaded replies (via parent_id)
 * - Helpful vote tracking
 * - Polymorphic author (works with any user type)
 *
 * @property int $id
 * @property int $product_id
 * @property int $authorable_id
 * @property string $authorable_type
 * @property int|null $parent_id
 * @property int|null $rating (1-5)
 * @property string|null $review
 * @property int $helpful_votes
 */
class ProductEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'authorable_id',
        'authorable_type',
        'parent_id',
        'rating',
        'review',
        'helpful_votes',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'helpful_votes' => 'integer',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductEngagement::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProductEngagement::class, 'parent_id');
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Only top-level reviews (not replies)
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Only reviews with rating
     */
    public function scopeWithRating(Builder $query): Builder
    {
        return $query->whereNotNull('rating');
    }

    /**
     * Only reviews with text
     */
    public function scopeWithReview(Builder $query): Builder
    {
        return $query->whereNotNull('review');
    }

    /**
     * Order by most helpful
     */
    public function scopeMostHelpful(Builder $query): Builder
    {
        return $query->orderByDesc('helpful_votes');
    }

    /**
     * Order by newest first
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    public function isReply(): bool
    {
        return ! is_null($this->parent_id);
    }

    public function hasRating(): bool
    {
        return ! is_null($this->rating);
    }

    public function hasReview(): bool
    {
        return ! is_null($this->review) && trim($this->review) !== '';
    }

    public function incrementHelpful(): void
    {
        $this->increment('helpful_votes');
    }
}
