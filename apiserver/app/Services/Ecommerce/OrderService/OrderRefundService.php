<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\OrderService;

use App\Casts\ShipmentStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Events\RefundProcessed;
use App\Jobs\SendSmsJob;
use App\Models\Admin;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Shipment;
use App\Models\Ecommerce\ShipmentItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\GeneralNotification;
use App\Services\Affiliate\AffiliateVolumeService;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\MoneyService;
use App\Services\UserServices\UserWalletService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class OrderRefundService
{
    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly AffiliateVolumeService $volumeService,
    ) {}

    public static function make(): self
    {
        return new self(
            app(UserWalletService::class),
            app(AffiliateVolumeService::class)
        );
    }

    public function canBeRefunded(OrderItem $orderItem): bool
    {
        return $this->getRefundEligibility($orderItem)['eligible'];
    }

    public function requestReturn(User $user, OrderItem $orderItem, ?string $reason = null): array
    {
        $eligibility = $this->getReturnEligibility($orderItem);
        if (! $eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['message'],
            ];
        }

        $shipments = $this->getItemShipments($orderItem);
        if ($shipments->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Shipment not found for this order item.',
            ];
        }

        $created = [];

        DB::transaction(function () use ($shipments, $orderItem, $reason, &$created) {
            foreach ($shipments as $shipment) {
                $pivot = $shipment->shipmentItems()
                    ->where('order_item_id', $orderItem->id)
                    ->first();

                $quantity = (int) ($pivot?->quantity ?? $orderItem->quantity);

                $returnShipment = $orderItem->order->shipments()->create([
                    'pickup_address_id' => $orderItem->order->shipping_address_id,
                    'delivery_address_id' => $shipment->pickup_address_id,
                    'total_quantity' => $quantity,
                    'status' => ShipmentStatusCast::RETURNING->value,
                    'provider' => 'native',
                    'return_initiated_at' => now(),
                ]);

                $returnShipment->shipmentItems()->create([
                    'order_item_id' => $orderItem->id,
                    'quantity' => $quantity,
                ]);

                $created[] = $returnShipment;
            }
        });

        $this->notifyReturnRequested($user, $orderItem, $reason);

        return [
            'success' => true,
            'message' => 'Return request accepted. Pickup will be scheduled.',
            'data' => [
                'return_shipments' => collect($created)->pluck('id')->all(),
            ],
        ];
    }

    public function requestRefund(User $user, OrderItem $orderItem, ?string $reason = null): array
    {
        $eligibility = $this->getRefundEligibility($orderItem);
        if (! $eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['message'],
            ];
        }

        $order = $orderItem->order;
        $transaction = $order->transaction;

        if (! $transaction || ! $transaction->isCompleted() || ! $transaction->verified) {
            return [
                'success' => false,
                'message' => 'Payment not completed or verified.',
            ];
        }

        $refundAmount = $this->calculateRefundAmount($orderItem);
        if ($refundAmount <= 0) {
            return [
                'success' => false,
                'message' => 'Refund amount is zero for this item.',
            ];
        }

        $wallet = $this->walletService->getOrCreateWallet($user);

        $refund = Transaction::create([
            'wallet_id' => $wallet->id,
            'transactionable_type' => get_class($order),
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::REFUND,
            'status' => TransactionStatusCast::PENDING,
            'amount' => $refundAmount,
            'fee' => 0,
            'tax' => 0,
            'net_amount' => $refundAmount,
            'currency' => $wallet->currency,
            'purpose' => 'refund',
            'description' => $reason ?? 'Refund requested',
            'parent_transaction_id' => $transaction->id,
            'verified' => false,
            'metadata' => [
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'requested_at' => now()->toIso8601String(),
            ],
        ]);

        $this->notifyRefundRequested($user, $orderItem, $refundAmount);

        return [
            'success' => true,
            'message' => 'Refund request accepted. It will be processed after approval.',
            'data' => [
                'refund_transaction_uuid' => $refund->uuid,
                'amount' => $refundAmount,
                'amount_formatted' => MoneyService::format($refundAmount),
                'status' => $refund->status->value,
            ],
        ];
    }

    private function getRefundEligibility(OrderItem $orderItem): array
    {
        $eligibility = $this->getReturnEligibility($orderItem, true);
        if (! $eligibility['eligible']) {
            return $eligibility;
        }

        $shipments = $this->getItemShipments($orderItem);
        if ($shipments->isEmpty()) {
            return ['eligible' => false, 'message' => 'Shipment not found for this order item.'];
        }

        $allReturned = $shipments->every(fn (Shipment $shipment) => $shipment->status === ShipmentStatusCast::RETURNED);
        if (! $allReturned) {
            return ['eligible' => false, 'message' => 'Return is not completed yet.'];
        }

        return ['eligible' => true, 'message' => 'Eligible for refund.'];
    }

    public function approveRefund(Transaction $refundTransaction): bool
    {
        if ($refundTransaction->type !== TransactionTypeCast::REFUND) {
            return false;
        }

        if ($refundTransaction->status !== TransactionStatusCast::PENDING) {
            return false;
        }

        $wallet = $refundTransaction->wallet;
        if (! $wallet) {
            return false;
        }

        return DB::transaction(function () use ($refundTransaction, $wallet) {
            $wallet->increment('balance', $refundTransaction->amount);
            $wallet->increment('total_credited', $refundTransaction->amount);

            $refundTransaction->update([
                'status' => TransactionStatusCast::COMPLETED,
                'verified' => true,
                'verified_at' => now(),
                'balance_after' => $wallet->balance,
            ]);

            if ($refundTransaction->transactionable instanceof OrderItem) {
                $this->volumeService->reverseForOrderItem(
                    $refundTransaction->transactionable,
                    'refund_approved'
                );
            }

            event(new RefundProcessed($refundTransaction));

            return true;
        });
    }

    private function getReturnEligibility(OrderItem $orderItem, bool $allowReturned = false): array
    {
        $order = $orderItem->order;
        $product = $orderItem->product;

        if (! $order || ! $product) {
            return ['eligible' => false, 'message' => 'Order item is invalid.'];
        }

        $returnDays = (int) ($product->return_days ?? 0);
        if ($returnDays <= 0 || ! $product->is_returnable) {
            return ['eligible' => false, 'message' => 'This product is not returnable.'];
        }

        $shipments = $this->getItemShipments($orderItem);
        if ($shipments->isEmpty()) {
            return ['eligible' => false, 'message' => 'Shipment not found for this order item.'];
        }

        if (! $allowReturned) {
        if (! $allowReturned) {
            $alreadyReturning = $shipments->contains(fn (Shipment $shipment) => in_array($shipment->status, [
                ShipmentStatusCast::RETURNING,
                ShipmentStatusCast::RETURNED,
            ], true));

            if ($alreadyReturning) {
                return ['eligible' => false, 'message' => 'Return already initiated for this item.'];
            }
        }
        }

        $shippedAt = $shipments->max('shipped_at');
        if (! $shippedAt) {
            return ['eligible' => false, 'message' => 'Item is not shipped yet.'];
        }

        $eligibleFrom = $shippedAt->copy()->addHours(24);
        $eligibleUntil = $shippedAt->copy()->addDays($returnDays);

        if (now()->lt($eligibleFrom)) {
            return ['eligible' => false, 'message' => 'Return window opens 24 hours after shipment.'];
        }

        if (now()->gt($eligibleUntil)) {
            return ['eligible' => false, 'message' => 'Return window has expired.'];
        }

        return ['eligible' => true, 'message' => 'Eligible for return/refund.'];
    }

    private function getItemShipments(OrderItem $orderItem)
    {
        return $orderItem->shipments()->with('shipmentItems')->get();
    }

    private function calculateRefundAmount(OrderItem $orderItem): int
    {
        $order = $orderItem->order;
        if (! $order || $order->subtotal <= 0) {
            return (int) $orderItem->total_price;
        }

        $itemSubtotal = (int) $orderItem->total_price;
        $ratio = $itemSubtotal / max(1, (int) $order->subtotal);
        $taxShare = (int) round($order->tax * $ratio);

        return max(0, $itemSubtotal + $taxShare);
    }

    private function notifyReturnRequested(User $user, OrderItem $orderItem, ?string $reason = null): void
    {
        $order = $orderItem->order;
        $message = 'Return request received for Order #'.$order->order_number.'.';

        $user->notify(new GeneralNotification(
            title: 'Return request received',
            message: $message,
            actionUrl: rtrim((string) config('app.client_url'), '/').'/order/'.$order->uuid,
            actionText: 'View Order',
            channels: ['database', 'push', 'mail'],
            type: 'info'
        ));

        if ($user->mobile) {
            $sms = SmsRequest::single(
                $user->mobile,
                'Return request received. We will arrange pickup soon.'
            );
            SendSmsJob::dispatch($sms);
        }

        Notification::make()
            ->title('Return requested')
            ->body('Order #'.$order->order_number.' return requested.')
            ->sendToDatabase(Admin::all());
    }

    private function notifyRefundRequested(User $user, OrderItem $orderItem, int $amount): void
    {
        $order = $orderItem->order;
        $message = 'Refund request received for Order #'.$order->order_number.'.';

        $user->notify(new GeneralNotification(
            title: 'Refund request received',
            message: $message.' Amount: '.MoneyService::format($amount),
            actionUrl: rtrim((string) config('app.client_url'), '/').'/order/'.$order->uuid,
            actionText: 'View Order',
            channels: ['database', 'push', 'mail'],
            type: 'info'
        ));

        if ($user->mobile) {
            $sms = SmsRequest::single(
                $user->mobile,
                'Refund request received. Processing after approval.'
            );
            SendSmsJob::dispatch($sms);
        }

        Notification::make()
            ->title('Refund requested')
            ->body('Order #'.$order->order_number.' refund requested.')
            ->sendToDatabase(Admin::all());
    }
}
