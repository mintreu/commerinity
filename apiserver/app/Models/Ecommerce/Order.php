<?php

namespace App\Models;

use App\Casts\OrderStatusCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\OrderInvoice;
use App\Models\Shipment;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total',
        'shipping_address_id',
        'billing_address_id',
        'notes',
        'admin_notes',
        'expire_at',
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
            'expire_at' => 'datetime'
        ];
    }

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
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
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(OrderInvoice::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

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

    private function getStatusValue(): ?string
    {
        return $this->status instanceof OrderStatusCast ? $this->status->value : $this->status;
    }
}
