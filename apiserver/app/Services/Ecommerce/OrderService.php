<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\OrderStatusCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\Affiliate\CommissionProcessorService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected array $errors = [];

    protected bool $asDraft = false;

    public function __construct(
        protected CartService $cartService,
        protected ?CommissionProcessorService $commissionProcessor = null
    ) {}

    /**
     * Create order as draft (no payment processing)
     */
    public function draft(): static
    {
        $this->asDraft = true;

        return $this;
    }

    /**
     * Create order from cart
     */
    public function createFromCart(
        User|Model $customer,
        int $shippingAddressId,
        ?int $billingAddressId = null,
        ?string $notes = null
    ): Order|false {
        try {
            // Get shipping address
            $shippingAddress = Address::find($shippingAddressId);
            if (! $shippingAddress) {
                $this->errors[] = 'Invalid shipping address';

                return false;
            }

            // Validate cart
            $validation = $this->cartService->validate($shippingAddress);
            if (! $validation['valid']) {
                $this->errors = $validation['errors'];

                return false;
            }

            $cartTotal = $validation['cart_total'];

            if (empty($cartTotal['items'])) {
                $this->errors[] = 'Cart is empty';

                return false;
            }

            return DB::transaction(function () use (
                $customer,
                $shippingAddressId,
                $billingAddressId,
                $notes,
                $cartTotal
            ) {
                // Create order
                $order = Order::create([
                    'customerable_type' => get_class($customer),
                    'customerable_id' => $customer->id,
                    'status' => OrderStatusCast::PENDING->value,
                    'subtotal' => $cartTotal['subtotal'],
                    'shipping_cost' => $cartTotal['shipping_cost'],
                    'tax' => $cartTotal['tax'],
                    'discount' => $cartTotal['discount'],
                    'total' => $cartTotal['total'],
                    'total_bv' => $cartTotal['bv'],
                    'total_pv' => $cartTotal['pv'],
                    'total_reward_points' => $cartTotal['reward_points'],
                    'quantity' => $cartTotal['total_quantity'],
                    'shipping_address_id' => $shippingAddressId,
                    'billing_address_id' => $billingAddressId ?? $shippingAddressId,
                    'voucher' => $cartTotal['coupon_code'],
                    'notes' => $notes,
                    'expire_at' => now()->addMinutes((int) config('cart.order_expiry_minutes', 30)),
                ]);

                // Create order items from cart data
                foreach ($cartTotal['items'] as $item) {
                    // Create order items for each stock entry (FIFO tracking)
                    foreach ($item['stock_entries'] as $stockEntry) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'stock_id' => $stockEntry['stock_id'],
                            'product_name' => $item['product_name'],
                            'product_sku' => $item['product_sku'],
                            'quantity' => $stockEntry['quantity'],
                            'unit_price' => $stockEntry['unit_price'],
                            'total_price' => $stockEntry['line_total'],
                            'bv' => $stockEntry['bv'],
                            'pv' => $stockEntry['pv'],
                            'reward_points' => $stockEntry['reward_points'],
                        ]);
                    }
                }

                // Consume stock (FIFO)
                if (! $this->consumeStock($cartTotal['stock_allocations'])) {
                    throw new Exception('Failed to consume stock');
                }

                // Clear cart after successful order
                $this->cartService->empty();

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Order creation failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->errors[] = 'Failed to create order: '.$e->getMessage();

            return false;
        }
    }

    /**
     * Consume stock based on allocations
     */
    protected function consumeStock(array $stockAllocations): bool
    {
        foreach ($stockAllocations as $productId => $entries) {
            foreach ($entries as $entry) {
                $stock = ProductStock::find($entry['stock_id']);
                if (! $stock) {
                    $this->errors[] = "Stock entry not found: {$entry['stock_id']}";

                    return false;
                }

                if (! $stock->consumeStock($entry['quantity'])) {
                    $this->errors[] = "Insufficient stock for product {$productId}";

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Confirm order (after payment)
     * Note: Affiliate commissions are processed only when order is DELIVERED, not on confirmation
     */
    public function confirmOrder(Order $order): bool
    {
        try {
            if (! $order->isPending()) {
                $this->errors[] = 'Order is not in pending status';

                return false;
            }

            $order->update([
                'status' => OrderStatusCast::CONFIRMED->value,
                'payment_success' => true,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Order confirmation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $this->errors[] = 'Failed to confirm order';

            return false;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        try {
            if (! $order->canBeCancelled()) {
                $this->errors[] = 'Order cannot be cancelled';

                return false;
            }

            return DB::transaction(function () use ($order, $reason) {
                // Restore stock
                foreach ($order->items as $item) {
                    if ($item->stock_id) {
                        $stock = ProductStock::find($item->stock_id);
                        if ($stock) {
                            $stock->decrement('sold_quantity', $item->quantity);
                        }
                    }
                }

                $order->update([
                    'status' => OrderStatusCast::CANCELLED->value,
                    'admin_notes' => $reason,
                ]);

                return true;
            });
        } catch (Exception $e) {
            Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $this->errors[] = 'Failed to cancel order';

            return false;
        }
    }

    /**
     * Update order status
     *
     * Status Flow: PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED → COMPLETED
     * Affiliate commissions are processed ONLY when order reaches COMPLETED status
     * (after return period expires)
     */
    public function updateStatus(Order $order, OrderStatusCast $status): bool
    {
        try {
            $updateData = ['status' => $status->value];

            // Set delivered_at and calculate return_period_ends_at when marking as DELIVERED
            if ($status === OrderStatusCast::DELIVERED) {
                $now = now();
                $maxReturnDays = $order->getMaxReturnDays();

                $updateData['delivered_at'] = $now;
                $updateData['return_period_ends_at'] = $maxReturnDays > 0
                    ? $now->copy()->addDays($maxReturnDays)
                    : $now; // If no returnable products, complete immediately
            }

            // Set completed_at when marking as COMPLETED
            if ($status === OrderStatusCast::COMPLETED) {
                $updateData['completed_at'] = now();
            }

            $order->update($updateData);

            // Process Affiliate commissions ONLY on COMPLETED (after return period)
            // This ensures customer cannot return goods after commissions are paid
            if ($status === OrderStatusCast::COMPLETED) {
                $this->processOrderCommissions($order);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Order status update failed', [
                'order_id' => $order->id,
                'status' => $status->value,
                'error' => $e->getMessage(),
            ]);

            $this->errors[] = 'Failed to update order status';

            return false;
        }
    }

    /**
     * Mark order as delivered
     * Sets delivered_at and calculates return_period_ends_at based on product return days
     * Commission processing happens later when order is COMPLETED (after return period)
     */
    public function markAsDelivered(Order $order): bool
    {
        return $this->updateStatus($order, OrderStatusCast::DELIVERED);
    }

    /**
     * Mark order as completed and process commissions
     * Should only be called after return period has expired
     */
    public function markAsCompleted(Order $order): bool
    {
        if (! $order->isDelivered()) {
            $this->errors[] = 'Order must be delivered before it can be completed';

            return false;
        }

        return $this->updateStatus($order, OrderStatusCast::COMPLETED);
    }

    /**
     * Process Affiliate commissions for completed order
     * Only processes for subscribed members with BV > 0
     * Called when order status becomes COMPLETED (after return period)
     */
    public function processOrderCommissions(Order $order): bool
    {
        // Skip if already processed
        if ($order->isCommissionProcessed()) {
            return true;
        }

        // Skip if order cannot generate commissions
        if (! $order->canGenerateCommission()) {
            Log::info('Order skipped for commission - not eligible', [
                'order_id' => $order->id,
                'total_bv' => $order->total_bv,
                'customer_type' => $order->customerable_type,
            ]);

            return true;
        }

        try {
            // Get or create commission processor
            $processor = $this->commissionProcessor ?? app(CommissionProcessorService::class);

            // Process commissions asynchronously (via queue)
            $processor->processAsync($order);

            // Mark as processed
            $order->markCommissionProcessed();

            Log::info('Order commission processing queued', [
                'order_id' => $order->id,
                'total_bv' => $order->total_bv,
                'customer_id' => $order->customerable_id,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Order commission processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get order by UUID
     */
    public function getOrder(string $uuid): ?Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product', 'shippingAddress', 'billingAddress', 'payments'])
            ->first();
    }

    /**
     * Get orders for customer
     */
    public function getCustomerOrders(User|Model $customer, int $limit = 10)
    {
        return Order::forCustomer($customer)
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are errors
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
