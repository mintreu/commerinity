<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService;

use App\Casts\GstTaxCast;
use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\VoucherCode;
use App\Models\User;
use App\Services\Ecommerce\CartService\Support\HasGuestCartSupport;
use App\Services\Ecommerce\CartService\Support\HasVoucherCodeValidator;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{
    use HasGuestCartSupport;
    use HasVoucherCodeValidator;

    protected bool $changed = false;

    protected User|Model|null $customer;

    protected Request $request;

    protected ?string $error = null;

    protected array $meta = [];

    protected int $totalQuantity = 0;

    protected bool $hasGuest = false;

    protected ?string $guestId = null;

    protected ?string $token = null;

    protected bool $validToken = false;

    protected int $tokenTTL = 3600 * 24 * 7; // 7 days default

    protected ?string $couponCode = null;

    protected bool $validCoupon = false;

    public function __construct(User|Model|null $user = null)
    {
        $this->customer = $user;
    }

    /**
     * Capture request headers for guest cart support
     */
    public function capture(Request $request): self
    {
        $this->request = $request;

        $headerId = config('cart.guest.header_id', 'x-guest-id');
        $headerToken = config('cart.guest.header_token', 'x-guest-token');

        $this->guestId = $request->header($headerId);
        $this->token = $request->header($headerToken);

        $this->tokenTTL = (int) config('cart.guest.token_ttl_seconds', $this->tokenTTL);

        if ($this->guestId) {
            $this->hasGuest = true;
            $this->validToken = $this->validateGuestToken($this->guestId, $this->token);
        }

        // Merge guest cart to customer on login
        if (! is_null($this->customer) && $this->hasGuest && $this->validToken) {
            $this->mergeGuestCartToCustomer();
        }

        return $this;
    }

    /**
     * Get cart items for current customer or guest
     */
    public function items(): ?Collection
    {
        if ($this->customer) {
            return Cart::where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id)
                ->with('cartable')
                ->get();
        }

        if ($this->hasGuest && $this->validToken) {
            return Cart::where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token)
                ->with('cartable')
                ->get();
        }

        $this->setError('Cart credentials not validated!');

        return null;
    }

    /**
     * Get cart items with stock info (eager loaded)
     */
    public function itemsWithStock(?Address $shippingAddress = null): ?Collection
    {
        $items = $this->items();

        if (! $items) {
            return null;
        }

        // Load stock for each product, prioritizing nearest to shipping address
        $items->load(['cartable.stocks' => function ($query): void {
            $query->inStock()->fifo();
        }]);

        return $items;
    }

    /**
     * Add item to cart
     */
    public function add(Model|Product $item, int $quantity): self
    {
        if (is_null($this->customer) && ! $this->hasGuest) {
            $this->setError('Cart credentials not validated!');

            return $this;
        }

        $maxPerOrder = (int) config('cart.limits.max_per_order', 10);
        $approvedQuantity = min($quantity, $maxPerOrder);

        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        }

        $existing = $query->first();

        if ($existing) {
            $newQty = min($existing->quantity + $quantity, $maxPerOrder);
            $existing->update(['quantity' => $newQty]);
        } else {
            Cart::create([
                'cartable_id' => $item->id,
                'cartable_type' => get_class($item),
                'quantity' => $approvedQuantity,
                'ownerable_type' => $this->customer ? get_class($this->customer) : null,
                'ownerable_id' => $this->customer?->id,
                'is_guest' => ! $this->customer,
                'guest_id' => $this->guestId,
                'guest_token' => $this->token,
            ]);
        }

        $this->changed = true;

        return $this;
    }

    /**
     * Update cart item quantity
     */
    public function update(Model|Product $item, int $quantity): self
    {
        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        } else {
            $this->setError('Cart credentials not validated!');

            return $this;
        }

        $cart = $query->first();

        if (! $cart) {
            return $this->add($item, $quantity);
        }

        $maxPerOrder = (int) config('cart.limits.max_per_order', 10);
        $cart->update([
            'quantity' => min($quantity, $maxPerOrder),
        ]);

        $this->changed = true;

        return $this;
    }

    /**
     * Remove item from cart
     */
    public function delete(Model $item): self
    {
        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        } else {
            $this->setError('Cart credentials not validated!');

            return $this;
        }

        $query->delete();
        $this->changed = true;

        return $this;
    }

    /**
     * Empty entire cart
     */
    public function empty(): self
    {
        if ($this->customer) {
            Cart::where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id)
                ->delete();
        } elseif ($this->hasGuest && $this->validToken) {
            Cart::where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token)
                ->delete();
        }

        $this->changed = true;

        return $this;
    }

    /**
     * Get cart totals with FIFO stock allocation
     *
     * @param  Address|null  $shippingAddress  Shipping address for tax/stock calculations
     * @return array Cart summary with subtotal, tax, shipping, discount, total
     */
    public function getCartTotal(?Address $shippingAddress = null): array
    {
        $cartItems = $this->itemsWithStock($shippingAddress);



        if (! $cartItems || $cartItems->isEmpty()) {
            return $this->emptyCartTotal();
        }

        $subtotal = 0;
        $totalTax = 0;
        $totalBv = 0;
        $totalPv = 0;
        $totalRewardPoints = 0;
        $itemBreakdown = [];
        $stockAllocations = [];
        $shippingState = $shippingAddress?->state
            ? Str::lower(trim($shippingAddress->state->name))
            : null;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->cartable;
            if (! $product instanceof Product) {
                continue;
            }

            $quantity = $cartItem->quantity;

            // FIFO stock allocation
            $allocation = $this->allocateStockFifo($product, $quantity, $shippingState);

            if ($allocation['allocated_quantity'] < $quantity) {
                // Not enough stock - record warning
                $this->meta['warnings'][] = "Only {$allocation['allocated_quantity']} units available for {$product->name}";
            }

            $itemTotal = $allocation['total_price'];
            $subtotal += $itemTotal;



            // Calculate tax based on warehouse state
            $itemTax = 0;
            foreach ($allocation['stock_entries'] as $entry) {
                $warehouseState = $entry['warehouse_state'];
                $gstType = GstTaxCast::determineTaxType($shippingState, $warehouseState);
                $gstPercentage = $product->gst_tax_type?->percentage() ?? 0;

                if ($gstPercentage > 0 && $shippingState && $warehouseState) {
                    $itemTax += (int) round($entry['line_total'] * $gstPercentage / 100);
                }
            }

            $totalTax += $itemTax;
            $totalBv += $allocation['total_bv'];
            $totalPv += $allocation['total_pv'];
            $totalRewardPoints += $allocation['total_reward_points'];

            $itemBreakdown[] = [
                'cart_id' => $cartItem->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'requested_quantity' => $quantity,
                'allocated_quantity' => $allocation['allocated_quantity'],
                'unit_price' => $allocation['avg_unit_price'],
                'item_total' => $itemTotal,
                'item_tax' => $itemTax,
                'bv' => $allocation['total_bv'],
                'pv' => $allocation['total_pv'],
                'reward_points' => $allocation['total_reward_points'],
                'stock_entries' => $allocation['stock_entries'],
            ];

            $stockAllocations[$product->id] = $allocation['stock_entries'];
        }

        // Calculate discount from voucher
        $discount = 0;
        if ($this->couponCode && $this->validCoupon) {
            $voucher = VoucherCode::where('code', $this->couponCode)->first()?->voucher;
            if ($voucher && $voucher->canBeUsed()) {
                $discount = $voucher->calculateDiscount($subtotal);
            }
        }

        // Calculate shipping (TODO: integrate shipping service)
        $shippingCost = 0;

        $total = max(0, $subtotal + $totalTax + $shippingCost - $discount);

        return [
            'subtotal' => $subtotal,
            'tax' => $totalTax,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'bv' => $totalBv,
            'pv' => $totalPv,
            'reward_points' => $totalRewardPoints,
            'item_count' => $cartItems->count(),
            'total_quantity' => $cartItems->sum('quantity'),
            'items' => $itemBreakdown,
            'stock_allocations' => $stockAllocations,
            'coupon_code' => $this->couponCode,
            'shipping_address_id' => $shippingAddress?->id,
            'formatted' => [
                'subtotal' => MoneyService::format($subtotal),
                'tax' => MoneyService::format($totalTax),
                'shipping_cost' => MoneyService::format($shippingCost),
                'discount' => MoneyService::format($discount),
                'total' => MoneyService::format($total),
            ],
        ];
    }

    /**
     * Allocate stock using FIFO (First In First Out)
     * Prioritizes older stock entries and stock closer to shipping address
     */
    protected function allocateStockFifo(Product $product, int $quantity, ?string $shippingState): array
    {
        $stocks = $product->stocks()
            ->inStock()
            ->fifo()
            ->get();

        // Sort: prioritize same-state stocks first
        if ($shippingState) {
            $stocks = $stocks->sortBy(function ($stock) use ($shippingState) {
                $warehouseState = $stock->address?->state
                    ? Str::lower(trim($stock->address->state->name))
                    : null;

                return $warehouseState === $shippingState ? 0 : 1;
            });
        }

        $allocated = 0;
        $totalPrice = 0;
        $totalBv = 0;
        $totalPv = 0;
        $totalRewardPoints = 0;
        $stockEntries = [];

        foreach ($stocks as $stock) {
            if ($allocated >= $quantity) {
                break;
            }

            $available = $stock->available_stock;
            if ($available <= 0) {
                continue;
            }

            $toAllocate = min($available, $quantity - $allocated);
            $unitPrice = $stock->getEffectivePrice();
            $lineTotal = $unitPrice * $toAllocate;

            $stockEntries[] = [
                'stock_id' => $stock->id,
                'quantity' => $toAllocate,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'bv' => $stock->bv * $toAllocate,
                'pv' => $stock->pv * $toAllocate,
                'reward_points' => $stock->reward_points * $toAllocate,
                'warehouse_state' => $stock->address?->state
                    ? Str::lower(trim($stock->address->state))
                    : null,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date?->toDateString(),
            ];

            $allocated += $toAllocate;
            $totalPrice += $lineTotal;
            $totalBv += $stock->bv * $toAllocate;
            $totalPv += $stock->pv * $toAllocate;
            $totalRewardPoints += $stock->reward_points * $toAllocate;
        }

        return [
            'allocated_quantity' => $allocated,
            'total_price' => $totalPrice,
            'avg_unit_price' => $allocated > 0 ? (int) round($totalPrice / $allocated) : 0,
            'total_bv' => $totalBv,
            'total_pv' => $totalPv,
            'total_reward_points' => $totalRewardPoints,
            'stock_entries' => $stockEntries,
        ];
    }

    /**
     * Consume stock for cart (called when order is placed)
     * Returns true if all stock was successfully consumed
     */
    public function consumeCartStock(array $stockAllocations): bool
    {
        foreach ($stockAllocations as $productId => $entries) {
            foreach ($entries as $entry) {
                $stock = ProductStock::find($entry['stock_id']);
                if (! $stock || ! $stock->consumeStock($entry['quantity'])) {
                    $this->setError("Failed to consume stock for product {$productId}");

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate cart can be checked out
     */
    public function validate(?Address $shippingAddress = null): array
    {
        $cartTotal = $this->getCartTotal($shippingAddress);

        $errors = [];
        $warnings = $this->meta['warnings'] ?? [];

        if ($cartTotal['item_count'] === 0) {
            $errors[] = 'Cart is empty';
        }

        // Check stock availability
        foreach ($cartTotal['items'] as $item) {
            if ($item['allocated_quantity'] < $item['requested_quantity']) {
                $errors[] = "Insufficient stock for {$item['product_name']}. Only {$item['allocated_quantity']} available.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'cart_total' => $cartTotal,
        ];
    }

    protected function emptyCartTotal(): array
    {
        return [
            'subtotal' => 0,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 0,
            'bv' => 0,
            'pv' => 0,
            'reward_points' => 0,
            'item_count' => 0,
            'total_quantity' => 0,
            'items' => [],
            'stock_allocations' => [],
            'coupon_code' => null,
            'shipping_address_id' => null,
            'formatted' => [
                'subtotal' => MoneyService::format(0),
                'tax' => MoneyService::format(0),
                'shipping_cost' => MoneyService::format(0),
                'discount' => MoneyService::format(0),
                'total' => MoneyService::format(0),
            ],
        ];
    }

    /**
     * Get total quantity of items in cart
     */
    public function getTotalQuantity(): int
    {
        $items = $this->items();
        $this->totalQuantity = $items ? $items->sum('quantity') : 0;

        return $this->totalQuantity;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        $items = $this->items();

        return ! $items || $items->count() === 0;
    }

    /**
     * Get cart count (number of unique items)
     */
    public function count(): int
    {
        $items = $this->items();

        return $items ? $items->count() : 0;
    }

    public function setError(?string $msg): void
    {
        $this->error = $msg;
    }

    public function getErrors(): ?string
    {
        return $this->error;
    }

    public function hasErrors(): bool
    {
        return ! is_null($this->error);
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }

    public function getCustomer(): Model|User|null
    {
        return $this->customer;
    }

    public function isGuest(): bool
    {
        return $this->hasGuest && $this->validToken && is_null($this->customer);
    }

    public function getGuestId(): ?string
    {
        return $this->guestId;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }
}
