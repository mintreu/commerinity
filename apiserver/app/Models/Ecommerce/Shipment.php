<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\ShipmentStatusCast;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pickup_address_id',
        'delivery_address_id',
        'total_quantity',
        'status',
        'shipping_method',
        'provider',
        'shipping_provider_id',
        'provider_channel_id',
        'provider_order_id',
        'shipment_id',
        'tracking_id',
        'tracking_data',
        'shipment_track_activities',
        'last_update',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'return_initiated_at',
        'returned_at',
        'last_synced_at',
        'cod',
        'cod_amount',
        'cod_status',
        'cod_collected_at',
        'cod_remitted_at',
        'charge',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatusCast::class,
            'tracking_data' => 'array',
            'shipment_track_activities' => 'array',
            'last_update' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'return_initiated_at' => 'datetime',
            'returned_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'cod' => 'boolean',
            'cod_amount' => 'integer',
            'charge' => 'integer',
            'cod_collected_at' => 'datetime',
            'cod_remitted_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'pickup_address_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(OrderInvoice::class,'shipment_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'shipment_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === ShipmentStatusCast::DELIVERED;
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return $this->status === ShipmentStatusCast::IN_TRANSIT;
    }

    /**
     * Check if shipment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === ShipmentStatusCast::CANCELLED;
    }

    /**
     * Check if shipment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            ShipmentStatusCast::PROCESSING,
            ShipmentStatusCast::REVIEW,
            ShipmentStatusCast::PACKING,
            ShipmentStatusCast::READY_TO_SHIP,
        ], true);
    }
}
