<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\OrderStatusCast;
use App\Models\Address;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Order extends Model
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
        'shipping_address_id',
        'billing_address_id',
        'expire_at',
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
            'quantity' => 'integer',
            'payment_success' => 'boolean',
            'expire_at' => 'datetime',
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

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatusCast::CANCELLED->value);
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

    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    private function getStatusValue(): ?string
    {
        return $this->status instanceof OrderStatusCast ? $this->status->value : $this->status;
    }
}
