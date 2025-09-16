<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mintreu\LaravelGeokit\Traits\HasOrderAddresses;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class OrderShipment extends Model
{
    /** @use HasFactory<\Database\Factories\Order\OrderShipmentFactory> */
    use HasFactory,HasOrderAddresses;

    // shipment status
    public const PROCESSING = 'processing';

    public const REVIEW = 'review';

    public const PACKING = 'packing';

    public const READYTOSHIP = 'readytoship';

    public const INTRANSIT = 'intransit';

    public const DELIVERED = 'delivered';

    public const CANCELLED = 'cancelled';

    public const RETURNED = 'returned';

    public const RETURNING = 'returning';

    public const StatusOptions = [
        self::PROCESSING => 'Processing',
        self::REVIEW => 'Review',
        self::PACKING => 'Packing',
        self::READYTOSHIP => 'Ready To Ship',
        self::INTRANSIT => 'In Transit',
        self::DELIVERED => 'Delivered',
        self::CANCELLED => 'Cancelled',
        self::RETURNED => 'Returned',
        self::RETURNING => 'Return Placed',
    ];

    protected $fillable = [
        'total_quantity',
        'last_update',
        'status',
        'invoice_uid',
        'cod',
        'tracking_id',
        'tracking_data',
        'weight',
        'length',
        'breadth',
        'height',
        'charge',
        'provider_payment_method',
        'provider_channel_id',
        'provider_order_id',
        'shipment_id',
        'shipment_track_activities',
        'details',
        'order_id',
        'pickup_address',
        'delivery_address',
        'shipping_provider_id',
        'return_order_id',
        'return_shipment_id',
    ];

    protected $casts = [
        'tracking_data' => 'array',
        'shipment_track_activities' => 'array',
        'last_update' => 'array',
        'details' => 'array',
        'charge' => LaravelMoneyCast::class,
    ];

//    public function pickupAddress(): BelongsTo
//    {
//        return $this->belongsTo(Address::class, 'pickup_address', 'id');
//    }
//
//    public function deliveryAddress(): BelongsTo
//    {
//        return $this->belongsTo(Address::class, 'delivery_address', 'id');
//    }

    public function orderProducts(): BelongsToMany
    {
        return $this->belongsToMany(OrderProduct::class, 'shipment_products', 'order_shipment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(OrderInvoice::class, 'order_shipment_id', 'id');
    }


}
