<?php

namespace App\Models\Ecommerce;

use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\Ecommerce\OrderInvoiceFactory> */
    use HasFactory,HasUnique;


    protected $fillable = [
        'uuid',
        'order_id',
        'order_item_id',
        'shipment_id',
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
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }



}
