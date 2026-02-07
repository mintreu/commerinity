<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Address;
use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductStock - Purchase Entry Pattern
 *
 * Each stock record represents a purchase/inventory entry:
 * - Tracks landing cost for audit and purchase accounting
 * - Batch/lot tracking for inventory management
 * - FIFO consumption for sales
 *
 * @property int $id
 * @property int $product_id
 * @property int $init_quantity
 * @property int $sold_quantity
 * @property int $in_stock_quantity (computed)
 * @property bool $in_stock (computed)
 * @property int $priority
 * @property int|null $address_id
 * @property int $landing_cost (paise)
 * @property int|null $supplier_id
 * @property string|null $purchase_invoice_number
 * @property \Carbon\Carbon|null $purchase_date
 * @property \Carbon\Carbon|null $expiry_date
 * @property string|null $batch_number
 * @property string|null $notes
 */
class ProductStock extends Model
{
    use HasAddress,HasFactory;

    protected $fillable = [
        // Fixed column as it is
        'product_id',
        'init_quantity',
        'priority', // override manually priority of choosing stock when default using FIFO
        'low_stock_threshold',
        'notify_on_low_stock',
        'sold_quantity',
        // Address Type Pickup
        'address_id',

        // Supplier/Tracking // nullables
        'supplier_id',
        // Purchase Entry Fields nullables
        'purchase_invoice_number',
        'purchase_date',
        'landing_cost',
        'expiry_date',
        'batch_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'init_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'in_stock_quantity' => 'integer',
            'in_stock' => 'boolean',
            'low_stock_threshold' => 'integer',
            'notify_on_low_stock' => 'boolean',
            'priority' => 'integer',
            // Purchase Entry
            'landing_cost' => 'integer',
            // Dates
            'purchase_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // ========================================
    // Stock Management
    // ========================================

    public function inStock(): bool
    {
        return (bool) $this->in_stock;
    }

    public function minStock(int $count): int
    {
        return min($this->in_stock_quantity, $count);
    }

    public function isLowStock(): bool
    {
        return $this->in_stock_quantity <= $this->low_stock_threshold;
    }

    public function shouldNotify(): bool
    {
        return $this->notify_on_low_stock && $this->isLowStock();
    }

    /**
     * Get available stock (computed from init - sold)
     */
    public function getAvailableStockAttribute(): int
    {
        return (int) ($this->init_quantity - $this->sold_quantity);
    }

    /**
     * Consume stock from this entry (FIFO pattern)
     * Returns false if not enough stock available
     */
    public function consumeStock(int $qty): bool
    {
        if ($this->available_stock < $qty) {
            return false;
        }

        $this->increment('sold_quantity', $qty);

        return true;
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Filter to in-stock entries only
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('in_stock', true);
    }

    /**
     * Filter by batch number
     */
    public function scopeByBatch(Builder $query, string $batchNumber): Builder
    {
        return $query->where('batch_number', $batchNumber);
    }

    /**
     * Filter expiring stock (within days)
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    /**
     * Filter expired stock
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    /**
     * Order by FIFO (first in, first out)
     */
    public function scopeFifo(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }
}
