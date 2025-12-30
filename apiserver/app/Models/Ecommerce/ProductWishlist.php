<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ProductWishlist - User's wishlist/favorites
 *
 * @property int $id
 * @property int $product_id
 * @property int $authorable_id
 * @property string $authorable_type
 */
class ProductWishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'authorable_id',
        'authorable_type',
    ];

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
}
