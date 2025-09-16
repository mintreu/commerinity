<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\Toolkit\Traits\HasUnique;

class OrderInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\Order\OrderInvoiceFactory> */
    use HasFactory,HasUnique;


    protected $fillable = [
        'uuid',
        'order_id',
        'order_product_id',
        'order_shipment_id',
    ];


    protected static function booted()
    {
        static::creating(function ($user){
            $user->setUniqueCode('uuid',20,now()->year);
        });
        parent::booted();
    }




    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id', 'id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class, 'order_shipment_id', 'id');
    }


}
