<?php

namespace App\Models\Traits;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasOrder
{



    /**
     * MorphMany relationship to Cart.
     */
    public function orders():MorphMany
    {
        return $this->morphMany(Order::class, 'customerable');
    }


    public function pendingOrders(): MorphMany
    {
        return $this->orders()->where('status',OrderStatusCast::PENDING);
    }

    public function confirmOrders(): MorphMany
    {
        return $this->orders()->where('status',OrderStatusCast::CONFIRM);
    }

    public function completeOrders(): MorphMany
    {
        return $this->orders()->where('status',OrderStatusCast::COMPLETED);
    }

    public function refundedOrders(): MorphMany
    {
        return $this->orders()->where('status',OrderStatusCast::REFUNDED);
    }


    public function cancelOrders(): MorphMany
    {
        return $this->orders()->where('status',OrderStatusCast::CANCELLED);
    }


}
