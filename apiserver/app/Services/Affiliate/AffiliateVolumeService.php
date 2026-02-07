<?php

declare(strict_types=1);

namespace App\Services\Affiliate;

use App\Casts\AffiliateVolumeStatusCast;
use App\Casts\UserTypeCast;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\Affiliate\AffiliateVolumeLedger;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class AffiliateVolumeService
{
    /**
     * Record pending BV/PV for an order (buyer + upline).
     *
     * @return Collection<int, AffiliateVolumeLedger>
     */
    public function recordPendingForOrder(Order $order): Collection
    {
        $customer = $order->customer;
        if (! $customer instanceof User) {
            return collect();
        }

        if (! in_array($customer->type, [UserTypeCast::MEMBER, UserTypeCast::PROMOTER], true)) {
            return collect();
        }

        if ($order->total_bv <= 0 && $order->total_pv <= 0) {
            return collect();
        }

        $order->loadMissing(['items.product', 'customer', 'customer.genealogy']);

        $genealogy = $customer->genealogy ?: AffiliateGenealogy::forUser($customer->id);
        $uplines = $genealogy ? $genealogy->getUpline(4) : collect();

        $ledgers = collect();

        foreach ($order->items as $item) {
            if (! $item instanceof OrderItem) {
                continue;
            }

            if (($item->bv ?? 0) <= 0 && ($item->pv ?? 0) <= 0) {
                continue;
            }

            $eligibleAt = $this->resolveEligibleAt($item);

            $ledgers->push(AffiliateVolumeLedger::create([
                'user_id' => $customer->id,
                'source_type' => $order->getMorphClass(),
                'source_id' => $order->id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'depth' => 0,
                'bv' => (int) $item->bv,
                'pv' => (int) $item->pv,
                'status' => AffiliateVolumeStatusCast::PENDING,
                'eligible_at' => $eligibleAt,
                'meta' => [
                    'order_number' => $order->order_number,
                ],
            ]));

            foreach ($uplines as $index => $upline) {
                if (! $upline?->user_id) {
                    continue;
                }

                $ledgers->push(AffiliateVolumeLedger::create([
                    'user_id' => $upline->user_id,
                    'source_type' => $order->getMorphClass(),
                    'source_id' => $order->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'depth' => $index + 1,
                    'bv' => (int) $item->bv,
                    'pv' => (int) $item->pv,
                    'status' => AffiliateVolumeStatusCast::PENDING,
                    'eligible_at' => $eligibleAt,
                    'meta' => [
                        'order_number' => $order->order_number,
                        'source_user_id' => $customer->id,
                    ],
                ]));
            }
        }

        return $ledgers;
    }

    /**
     * Confirm all pending volumes for a given order.
     */
    public function confirmForOrder(Order $order): int
    {
        return AffiliateVolumeLedger::query()
            ->where('order_id', $order->id)
            ->where('status', AffiliateVolumeStatusCast::PENDING)
            ->update([
                'status' => AffiliateVolumeStatusCast::CONFIRMED,
                'confirmed_at' => now(),
            ]);
    }

    /**
     * Reverse volumes for an order (used for refunds/cancellations).
     */
    public function reverseForOrder(Order $order, string $reason = 'refund'): int
    {
        return AffiliateVolumeLedger::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [AffiliateVolumeStatusCast::PENDING, AffiliateVolumeStatusCast::CONFIRMED])
            ->update([
                'status' => AffiliateVolumeStatusCast::REVERSED,
                'reversed_at' => now(),
                'meta' => [
                    'reason' => $reason,
                ],
            ]);
    }

    /**
     * Reverse volumes for a specific order item.
     */
    public function reverseForOrderItem(OrderItem $item, string $reason = 'refund'): int
    {
        return AffiliateVolumeLedger::query()
            ->where('order_item_id', $item->id)
            ->whereIn('status', [AffiliateVolumeStatusCast::PENDING, AffiliateVolumeStatusCast::CONFIRMED])
            ->update([
                'status' => AffiliateVolumeStatusCast::REVERSED,
                'reversed_at' => now(),
                'meta' => [
                    'reason' => $reason,
                ],
            ]);
    }

    private function resolveEligibleAt(OrderItem $item): ?Carbon
    {
        $returnDays = (int) ($item->product?->return_days ?? 0);
        if ($returnDays <= 0) {
            return now();
        }

        return now()->addDays($returnDays);
    }
}
