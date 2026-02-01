<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\OrderService;

use App\Casts\OrderStatusCast;
use App\Casts\ShipmentStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderInvoice;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\Shipment;
use App\Models\Ecommerce\ShipmentItem;
use App\Models\Transaction;
use App\Services\Affiliate\CommissionProcessorService;
use App\Services\Ecommerce\PriceCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderValidationService
{
    protected Transaction $transaction;

    protected Order $order;

    protected ?CommissionProcessorService $commissionProcessor = null;

    public function __construct(
        Transaction $transaction,
        Order $order,
        ?CommissionProcessorService $commissionProcessor = null
    ) {
        $this->transaction = $transaction;
        $this->order = $order;
        $this->commissionProcessor = $commissionProcessor;
        $this->order->load(['items.product', 'items.stock', 'shippingAddress']);
    }

    public static function make(Transaction $transaction, Order $order, ?CommissionProcessorService $commissionProcessor = null): self
    {
        return new self($transaction, $order, $commissionProcessor);
    }

    public function validate(): void
    {
        DB::transaction(function () {
            foreach ($this->order->items as $item) {
                $this->processOrderItem($item);
            }

            // Update order status to CONFIRMED
            $this->order->update([
                'status' => OrderStatusCast::CONFIRMED->value,
                'payment_success' => true,
            ]);

            // Process commissions if CommissionProcessorService is provided
            if ($this->commissionProcessor) {
                $this->processCommissions();
            }

            Log::info('Order confirmed successfully', [
                'order_id' => $this->order->id,
                'order_uuid' => $this->order->uuid,
                'transaction_id' => $this->transaction->uuid,
            ]);
        });
    }

    /**
     * Process affiliate commissions for the order
     * Creates commissions in PENDING status
     */
    protected function processCommissions(): void
    {
        $customer = $this->order->customer;

        // Only process if customer exists and has affiliate status
        if (! $customer || ! $this->customerIsAffiliate($customer)) {
            Log::info('Skipping commission processing - customer is not an affiliate', [
                'order_id' => $this->order->id,
                'customer_type' => $customer?->type?->value ?? 'null',
            ]);

            return;
        }

        // Process commissions asynchronously (non-blocking)
        // Commissions are created in PENDING status
        // They will be approved and paid to wallet when order is COMPLETED
        $this->commissionProcessor->processAsync($this->order, persistImmediately: false);

        Log::info('Commissions queued for processing', [
            'order_id' => $this->order->id,
            'customer_id' => $customer->id,
            'total_bv' => $this->order->total_bv,
        ]);
    }

    /**
     * Check if customer is eligible for affiliate commissions
     */
    protected function customerIsAffiliate(object $customer): bool
    {
        $type = $customer->type ?? null;

        return in_array($type, [
            \App\Casts\UserTypeCast::MEMBER,
            \App\Casts\UserTypeCast::PROMOTER,
            \App\Casts\UserTypeCast::ADVISOR,
            \App\Casts\UserTypeCast::MENTOR,
        ], true);
    }

    /**
     * Process a single order item:
     * 1. Consume stock from ProductStock entries (FIFO)
     * 2. Create shipment entries grouped by pickup address
     * 3. Create invoices for each shipment
     */
    private function processOrderItem(OrderItem $orderItem): void
    {
        $product = $orderItem->product;
        $requestedQuantity = $orderItem->quantity;
        $priceService = app(PriceCalculationService::class);
        $context = $priceService->resolveStockContext($this->order->shippingAddress);

        // Get available stock entries ordered by FIFO (priority then created_at)
        // Note: in_stock and in_stock_quantity are generated columns, use calculation instead
        $stockEntries = ProductStock::query()
            ->where('product_id', $product->id)
            ->whereColumn('sold_quantity', '<', 'init_quantity')
            ->with('address')
            ->orderBy('created_at')
            ->get();

        $orderedStocks = $priceService->getOrderedStocksForContext($stockEntries, $context);

        $remainingQuantity = $requestedQuantity;
        $stockAllocations = [];

        // Allocate stock using FIFO pattern
        foreach ($orderedStocks as $stock) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableQuantity = $stock->in_stock_quantity;
            $quantityToConsume = min($remainingQuantity, $availableQuantity);

            if ($quantityToConsume > 0) {
                // Consume stock
                $stock->increment('sold_quantity', $quantityToConsume);

                // Update in_stock flag if depleted
                if ($stock->sold_quantity >= $stock->init_quantity) {
                    $stock->update(['in_stock' => false]);
                }


                $stockAllocations[] = [
                    'stock_id' => $stock->id,
                    'quantity' => $quantityToConsume,
                    'pickup_address_id' => $stock->address_id,
                ];

                $remainingQuantity -= $quantityToConsume;

                Log::info('Stock consumed for order item', [
                    'order_item_id' => $orderItem->id,
                    'stock_id' => $stock->id,
                    'quantity' => $quantityToConsume,
                    'remaining_quantity' => $remainingQuantity,
                ]);
            }
        }

        if ($remainingQuantity > 0) {
            Log::error('Insufficient stock for order item', [
                'order_item_id' => $orderItem->id,
                'product_id' => $product->id,
                'requested_quantity' => $requestedQuantity,
                'short_quantity' => $remainingQuantity,
            ]);
            throw new \Exception("Insufficient stock for product: {$product->name}. Short by {$remainingQuantity} units.");
        }

        // Group stock allocations by pickup address for shipment creation
        $groupedAllocations = collect($stockAllocations)->groupBy('pickup_address_id');

        foreach ($groupedAllocations as $pickupAddressId => $allocations) {
            $totalQuantity = $allocations->sum('quantity');




            // Create shipment For Order
            $orderShipment = $this->order->shipments()->create([
                'pickup_address_id' => $pickupAddressId,
                'delivery_address_id' => $this->order->shipping_address_id,
                'total_quantity' => $this->order->quantity,
                'status'    => ShipmentStatusCast::PROCESSING->value,
//                    'shipping_method',
                'provider' => 'native',
//                    'shipping_provider_id',
//                    'provider_channel_id',
//                    'provider_order_id',
//                    'shipment_id',
//                    'tracking_id',
//                    'tracking_data',
//                    'shipment_track_activities',
//                    'last_update',
//                    'shipped_at',
//                    'delivered_at',
//                    'cancelled_at',
//                    'last_synced_at',
//                    'cod',
//                    'cod_amount',
//                    'cod_status',
//                    'cod_collected_at',
//                    'cod_remitted_at',
//                    'charge',
            ]);





            if (! $orderShipment) {
                Log::error('Failed to create shipment', [
                    'order_id' => $this->order->id,
                    'order_item_id' => $orderItem->id,
                    'pickup_address_id' => $pickupAddressId,
                ]);

                continue;
            }

            // Create shipment items linking stock entries to shipment
            foreach ($allocations as $allocation) {
                $orderItemShipment = $this->makeOrderShipment($orderShipment,$orderItem, $allocation['quantity']);
            }

            // Create invoice
            $invoice = $this->makeOrderInvoice($orderShipment, $orderItem);

            if (! $invoice) {
                Log::error('Failed to create invoice', [
                    'shipment_id' => $orderShipment->id,
                    'order_item_id' => $orderItem->id,
                ]);
            }
        }

        Log::info('Order item processed successfully', [
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'quantity' => $requestedQuantity,
            'shipments_created' => $groupedAllocations->count(),
        ]);
    }

    /**
     * Create shipment for order item
     */
    protected function makeOrderShipment(Shipment $shipment, OrderItem $orderItem, int $quantity): ?ShipmentItem
    {
        return $shipment->shipmentItems()->create([
           // 'shipment_id' => $shipment->id,
            'order_item_id' => $orderItem->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create invoice for shipment
     */
    protected function makeOrderInvoice(Shipment $shipment, OrderItem $orderItem): ?OrderInvoice
    {
        return $shipment->invoice()->create([
            'uuid' => 'INV_'.$this->order->uuid,
            'order_id' => $this->order->id,
            'order_item_id' => $orderItem->id,
        ]);
    }
}
