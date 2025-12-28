<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\OrderStatusCast;
use App\Contracts\Mlm\CommissionTrigger;
use App\Models\Address;
use App\Models\Membership\UserSubscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Order Model - E-commerce orders with MLM commission support
 *
 * Implements CommissionTrigger for purchase-based MLM commissions.
 * Commissions are calculated on total_bv (Business Volume) for subscribed members only.
 */
class Order extends Model implements CommissionTrigger
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_number',
        'customerable_type',
        'customerable_id',
        'status',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total',
        'total_bv',
        'total_pv',
        'total_reward_points',
        'commission_processed',
        'shipping_address_id',
        'billing_address_id',
        'expire_at',
        'delivered_at',
        'return_period_ends_at',
        'completed_at',
        'voucher',
        'tracking_id',
        'payment_success',
        'quantity',
        'notes',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusCast::class,
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'tax' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'total_bv' => 'integer',
            'total_pv' => 'integer',
            'total_reward_points' => 'integer',
            'commission_processed' => 'boolean',
            'quantity' => 'integer',
            'payment_success' => 'boolean',
            'expire_at' => 'datetime',
            'delivered_at' => 'datetime',
            'return_period_ends_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Get route key name for URL binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.strtoupper(Str::random(10));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Polymorphic customer relationship (User or any other model)
     */
    public function customerable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Transactions linked to this order
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transactionable_id')
            ->where('transactionable_type', self::class);
    }

    /**
     * Shipping address
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Billing address
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Shipments for this order
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', OrderStatusCast::PENDING->value);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', OrderStatusCast::CONFIRMED->value);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', OrderStatusCast::PROCESSING->value);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', OrderStatusCast::SHIPPED->value);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatusCast::DELIVERED->value);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatusCast::COMPLETED->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatusCast::CANCELLED->value);
    }

    /**
     * Scope to find delivered orders ready for completion (return period ended)
     */
    public function scopeReadyForCompletion($query)
    {
        return $query->where('status', OrderStatusCast::DELIVERED->value)
            ->whereNotNull('return_period_ends_at')
            ->where('return_period_ends_at', '<=', now());
    }

    public function scopeForCustomer($query, Model $customer)
    {
        return $query->where('customerable_type', $customer::class)
            ->where('customerable_id', $customer->id);
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::PENDING->value;
    }

    public function isConfirmed(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::CONFIRMED->value;
    }

    public function isProcessing(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::PROCESSING->value;
    }

    public function isShipped(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::SHIPPED->value;
    }

    public function isDelivered(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::DELIVERED->value;
    }

    public function isCompleted(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::COMPLETED->value;
    }

    public function isCancelled(): bool
    {
        return $this->getStatusValue() === OrderStatusCast::CANCELLED->value;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->getStatusValue(), [
            OrderStatusCast::PENDING->value,
            OrderStatusCast::CONFIRMED->value,
        ], true);
    }

    /**
     * Check if order is in return period (delivered but not yet completed)
     */
    public function isInReturnPeriod(): bool
    {
        return $this->isDelivered()
            && $this->return_period_ends_at
            && $this->return_period_ends_at->isFuture();
    }

    /**
     * Check if return period has ended and order can be completed
     */
    public function canBeCompleted(): bool
    {
        return $this->isDelivered()
            && $this->return_period_ends_at
            && $this->return_period_ends_at->isPast();
    }

    /**
     * Calculate maximum return days from all order items
     */
    public function getMaxReturnDays(): int
    {
        return $this->items()
            ->with('product')
            ->get()
            ->filter(fn ($item) => $item->product?->is_returnable)
            ->max(fn ($item) => $item->product?->return_days ?? 0) ?? 0;
    }

    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    private function getStatusValue(): ?string
    {
        return $this->status instanceof OrderStatusCast ? $this->status->value : $this->status;
    }

    // ========================================
    // CommissionTrigger Implementation
    // ========================================

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the commissionable amount (BV - Business Volume)
     * Returns total_bv which is sum of all order item BVs
     */
    public function getCommissionableAmount(): int
    {
        return $this->total_bv ?? 0;
    }

    /**
     * Get the user who triggered this order (customer)
     * Only Users can trigger commissions (not guests)
     */
    public function getTriggeringUserId(): int
    {
        if ($this->customerable_type === User::class) {
            return $this->customerable_id;
        }

        return 0;
    }

    /**
     * Get trigger type for commission calculation
     */
    public function getTriggerType(): string
    {
        return 'purchase';
    }

    public function getModel(): Model
    {
        return $this;
    }

    /**
     * Get commission context with order details
     */
    public function getCommissionContext(): array
    {
        return [
            'order_id' => $this->id,
            'order_number' => $this->order_number,
            'total' => $this->total,
            'total_bv' => $this->total_bv,
            'total_pv' => $this->total_pv,
            'total_reward_points' => $this->total_reward_points,
            'item_count' => $this->quantity,
            'status' => $this->getStatusValue(),
        ];
    }

    // ========================================
    // MLM Helper Methods
    // ========================================

    /**
     * Check if this order can generate MLM commissions
     * Only orders from subscribed members with BV > 0 qualify
     */
    public function canGenerateCommission(): bool
    {
        // Must have BV to generate commissions
        if ($this->total_bv <= 0) {
            return false;
        }

        // Must be from a User (not guest)
        if ($this->customerable_type !== User::class) {
            return false;
        }

        // Customer must have an active subscription
        $customer = $this->customerable;
        if (! $customer instanceof User) {
            return false;
        }

        return UserSubscription::where('user_id', $customer->id)
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Check if commission has already been processed
     */
    public function isCommissionProcessed(): bool
    {
        return $this->commission_processed === true;
    }

    /**
     * Mark commission as processed
     */
    public function markCommissionProcessed(): void
    {
        $this->update(['commission_processed' => true]);
    }

    /**
     * Get customer's active subscription (for MLM context)
     */
    public function getCustomerSubscription(): ?UserSubscription
    {
        if ($this->customerable_type !== User::class) {
            return null;
        }

        return UserSubscription::where('user_id', $this->customerable_id)
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->first();
    }
}
