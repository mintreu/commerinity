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
 * - Tracks landing cost, profit margin for accurate pricing
 * - BV/PV/reward_points for Affiliate commission calculations
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
    use HasFactory,HasAddress;

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
     * Check if quantity is within min/max range for this stock
     */
    public function isInRange(int $quantity): bool
    {
        return $quantity >= $this->min_quantity &&
            (is_null($this->max_quantity) || $quantity <= $this->max_quantity);
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
    // Price & Cost Calculations
    // ========================================

    /**
     * Get the effective price for this stock entry
     * Uses override price if set, otherwise calculates from landing cost + profit margin
     */
    public function getEffectivePrice(): int
    {
        if ($this->price !== null && $this->price > 0) {
            return $this->price;
        }

        return $this->calculatePriceFromCost();
    }

    private function calculatePriceFromCost(): int
    {
        if ($this->landing_cost <= 0) {
            return 0;
        }

        $marginMultiplier = 1 + ($this->profit_margin / 100);
        $price = (int) round($this->landing_cost * $marginMultiplier);

        return max(0, $price);
    }

    /**
     * Calculate profit amount per unit (in paise)
     */
    public function getProfitPerUnit(): int
    {
        $price = $this->getEffectivePrice();
        $cost = $this->landing_cost;

        return max(0, $price - $cost);
    }

    /**
     * Calculate actual profit margin percentage
     */
    public function getActualProfitMargin(): float
    {
        $cost = $this->landing_cost;
        if ($cost <= 0) {
            return 0.0;
        }

        return round(($this->getProfitPerUnit() / $cost) * 100, 2);
    }

    // ========================================
    // Affiliate Commission Helpers
    // ========================================

    /**
     * Check if this stock can generate Affiliate commissions
     */
    public function canGenerateCommission(): bool
    {
        return $this->is_commissionable && $this->bv > 0;
    }

    /**
     * Get commissionable amount for Affiliate calculations
     * Uses BV if set, otherwise calculates from price
     */
    public function getCommissionableAmount(): int
    {
        if ($this->bv > 0) {
            return $this->bv;
        }

        // Fallback: use profit as commissionable amount
        return $this->getProfitPerUnit();
    }

    /**
     * Calculate BV based on profit margin
     * Standard formula: BV = (profit * bv_percentage)
     */
    public static function calculateBvFromProfit(int $profitPaise, float $bvPercentage = 10.0): int
    {
        return (int) round($profitPaise * ($bvPercentage / 100));
    }

    /**
     * Calculate reward points from profit margin
     * Formula: reward_points = floor(profit / 100) (1 point per rupee profit)
     */
    public static function calculateRewardPoints(int $profitPaise): int
    {
        return (int) floor($profitPaise / 100);
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
     * Filter to commissionable stock entries
     */
    public function scopeCommissionable(Builder $query): Builder
    {
        return $query->where('is_commissionable', true)
            ->where('bv', '>', 0);
    }

    /**
     * Filter by quantity range (for wholesale pricing)
     */
    public function scopeForQuantity(Builder $query, int $quantity): Builder
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
