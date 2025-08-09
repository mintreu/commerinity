<?php

namespace Mintreu\LaravelProductCatalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'min_quantity',
        'max_quantity',
        'wholesale_unit_quantity',
        'price',
        'init_quantity',
        'sold_quantity',
        'address_id',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'price' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

//    public function address(): BelongsTo
//    {
//        return $this->belongsTo(Address::class, 'address_id');
//    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    public function scopeForQuantity($query, int $quantity)
    {
        return $query
            ->inStock()
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('created_at'); // FIFO-style
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */

    public function isInRange(int $quantity): bool
    {
        return $quantity >= $this->min_quantity &&
            (is_null($this->max_quantity) || $quantity <= $this->max_quantity);
    }

    public function getAvailableStockAttribute(): int
    {
        return (int) $this->stock;
    }

    public function getPricePerUnitAttribute(): float
    {
        return $this->price;
    }

    public function inStock(): bool
    {
        return $this->available_stock > 0;
    }

    public function minStock(int $count): int
    {
        return min($this->available_stock, $count);
    }

    /**
     * Consume stock from this tier. Returns false if not enough stock.
     */
    public function consumeStock(int $qty): bool
    {
        if ($this->available_stock < $qty) {
            return false;
        }

        $this->increment('sold_quantity', $qty);
        return true;
    }
}
