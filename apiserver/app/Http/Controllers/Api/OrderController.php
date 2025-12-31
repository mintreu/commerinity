<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\OrderStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderController - API for user orders
 *
 * Provides:
 * - List orders with pagination
 * - View single order details
 * - Order statistics/counts by status
 */
final class OrderController extends Controller
{
    /**
     * List user's orders with pagination
     *
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
            'status' => ['sometimes', 'string', 'in:pending,confirmed,processing,shipped,delivered,completed,cancelled'],
        ]);

        $user = $request->user();

        $query = Order::forCustomer($user)
            ->with(['items.product.media'])
            ->orderByDesc('created_at');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage);

        $formattedOrders = $orders->getCollection()->map(function (Order $order) {
            return $this->formatOrder($order);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedOrders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show single order details
     *
     * GET /api/orders/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $order = Order::forCustomer($user)
            ->where('uuid', $uuid)
            ->with(['items.product.media', 'shippingAddress', 'billingAddress', 'payments'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOrder($order, detailed: true),
        ]);
    }

    /**
     * Get order statistics for the user
     *
     * GET /api/orders/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total' => Order::forCustomer($user)->count(),
            'pending' => Order::forCustomer($user)->where('status', OrderStatusCast::PENDING->value)->count(),
            'processing' => Order::forCustomer($user)->where('status', OrderStatusCast::PROCESSING->value)->count(),
            'shipped' => Order::forCustomer($user)->where('status', OrderStatusCast::SHIPPED->value)->count(),
            'delivered' => Order::forCustomer($user)->where('status', OrderStatusCast::DELIVERED->value)->count(),
            'completed' => Order::forCustomer($user)->where('status', OrderStatusCast::COMPLETED->value)->count(),
            'cancelled' => Order::forCustomer($user)->where('status', OrderStatusCast::CANCELLED->value)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Format order for API response
     */
    private function formatOrder(Order $order, bool $detailed = false): array
    {
        $statusValue = $order->status instanceof OrderStatusCast ? $order->status->value : $order->status;

        $data = [
            'uuid' => $order->uuid,
            'order_number' => $order->order_number,
            'status' => $statusValue,
            'status_label' => $order->status instanceof OrderStatusCast ? $order->status->getLabel() : ucfirst($statusValue),
            'status_color' => $this->getStatusColor($statusValue),
            'subtotal' => $order->subtotal,
            'subtotal_formatted' => MoneyService::format($order->subtotal),
            'shipping_cost' => $order->shipping_cost,
            'shipping_cost_formatted' => MoneyService::format($order->shipping_cost),
            'tax' => $order->tax,
            'tax_formatted' => MoneyService::format($order->tax),
            'discount' => $order->discount,
            'discount_formatted' => MoneyService::format($order->discount),
            'total' => $order->total,
            'total_formatted' => MoneyService::format($order->total),
            'quantity' => $order->quantity,
            'payment_success' => $order->payment_success,
            'payment_status' => $order->payment_success ? 'paid' : 'pending',
            'tracking_id' => $order->tracking_id,
            'shipped_at' => $order->shipped_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'created_at' => $order->created_at->toIso8601String(),
            'created_at_formatted' => $order->created_at->toLocaleDateString('en-IN'),
            'items' => $order->items->map(fn (OrderItem $item) => $this->formatOrderItem($item)),
        ];

        if ($detailed) {
            $data['shipping_address'] = $order->shippingAddress ? [
                'name' => $order->shippingAddress->name,
                'phone' => $order->shippingAddress->phone,
                'address_line_1' => $order->shippingAddress->address_line_1,
                'address_line_2' => $order->shippingAddress->address_line_2,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'postal_code' => $order->shippingAddress->postal_code,
            ] : null;

            $data['payments'] = $order->payments->map(fn ($payment) => [
                'uuid' => $payment->uuid,
                'amount' => $payment->amount,
                'amount_formatted' => MoneyService::format($payment->amount),
                'status' => $payment->status->value,
                'method' => $payment->payment_method->value,
                'created_at' => $payment->created_at->toIso8601String(),
            ]);
        }

        return $data;
    }

    /**
     * Format order item for API response
     */
    private function formatOrderItem(OrderItem $item): array
    {
        $product = $item->product;

        return [
            'id' => (string) $item->id,
            'product_name' => $product?->name ?? 'Product',
            'product_slug' => $product?->url ?? '',
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'unit_price_formatted' => MoneyService::format($item->unit_price),
            'subtotal' => $item->unit_price * $item->quantity,
            'subtotal_formatted' => MoneyService::format($item->unit_price * $item->quantity),
            'image' => $product?->getFirstMediaUrl('product_display') ?: null,
        ];
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            OrderStatusCast::PENDING->value => 'warning',
            OrderStatusCast::CONFIRMED->value => 'info',
            OrderStatusCast::PROCESSING->value => 'info',
            OrderStatusCast::SHIPPED->value => 'info',
            OrderStatusCast::DELIVERED->value => 'success',
            OrderStatusCast::COMPLETED->value => 'success',
            OrderStatusCast::CANCELLED->value => 'error',
            default => 'neutral',
        };
    }
}
