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
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected array $errors = [];

    protected bool $asDraft = false;

    public function __construct(
        protected CartService $cartService
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

            // TODO: Trigger MLM commission calculation event
            // event(new OrderConfirmedEvent($order));

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
     */
    public function updateStatus(Order $order, OrderStatusCast $status): bool
    {
        try {
            $order->update(['status' => $status->value]);

            // Handle status-specific actions
            if ($status === OrderStatusCast::DELIVERED) {
                // Mark for commission processing
                if (! $order->commission_processed) {
                    // TODO: Process MLM commissions
                    // $this->processCommissions($order);
                }
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
