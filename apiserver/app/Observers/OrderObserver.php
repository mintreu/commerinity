<?php

declare(strict_types=1);

namespace App\Observers;

use App\Casts\OrderStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\User;
use App\Notifications\GeneralNotification;

final class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $customer = $order->customerable;
        if (! $customer instanceof User) {
            return;
        }

        $status = $order->status instanceof OrderStatusCast
            ? $order->status
            : OrderStatusCast::tryFrom((string) $order->status);

        if (! in_array($status, [OrderStatusCast::SHIPPED, OrderStatusCast::DELIVERED], true)) {
            return;
        }

        $orderRef = $order->order_number ?: $order->uuid;
        $orderUrl = rtrim((string) config('app.client_url'), '/').'/order/'.$order->uuid;

        $customer->notify(new GeneralNotification(
            title: 'Order Status Updated',
            message: "Your order {$orderRef} is now {$status->getLabel()}.",
            actionUrl: $orderUrl,
            actionText: 'Track Order',
            channels: ['database', 'push', 'mail'],
            type: $status === OrderStatusCast::DELIVERED ? 'success' : 'info',
        ));
    }
}

