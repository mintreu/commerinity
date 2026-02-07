<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService;

use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Casts\UserTypeCast;
use App\Models\Ecommerce\Product;
use App\Models\User;
use App\Services\Ecommerce\CartService\CartLineService;
use App\Services\Ecommerce\CartService\CartSaleValidator;
use App\Services\Ecommerce\CartService\CartVoucherValidator;
use App\Services\Ecommerce\CartService\Support\HasGuestCartSupport;
use App\Services\Ecommerce\CartService\Support\HasVoucherCodeValidator;
use App\Services\Ecommerce\PriceCalculationService;
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

        // Load stock for each product with addresses for context ordering
        $items->load(['cartable.stocks' => function ($query): void {
            $query->inStock()->with('address')->orderBy('created_at');
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
    public function cartCalculation(?Address $shippingAddress = null): array
    {
        $cartItems = $this->itemsWithStock($shippingAddress);

        if (! $cartItems || $cartItems->isEmpty()) {
            return $this->emptyCartTotal();
        }

        $subtotal = 0;
        $originalSubtotal = 0;
        $saleDiscountTotal = 0;
        $totalTax = 0;
        $totalBv = 0;
        $totalPv = 0;
        $totalRewardPoints = 0;
        $itemBreakdown = [];
        $stockAllocations = [];
        $taxBreakdown = [];
        $shippingState = $shippingAddress?->state
            ? Str::lower(trim($shippingAddress->state->name))
            : null;

        $lineMeta = [];

        $voucherValidator = null;
        if ($this->couponCode) {
            $preSubtotal = 0;
            $preQuantity = 0;
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->cartable;
                if (! $product instanceof Product) {
                    continue;
                }

                $preQuantity += $cartItem->quantity;
                $saleValidator = CartSaleValidator::make($this, $cartItem)
                    ->setResolvedPrice($product->getPrice());
                $saleValidator->validate();
                $salePrice = $saleValidator->getResolvedPrice();
                $preSubtotal += $salePrice * $cartItem->quantity;
            }

            $voucherValidator = CartVoucherValidator::make(
                $this,
                $this->couponCode,
                $this->customer,
                [
                    'shipping_address_id' => $shippingAddress?->id,
                    'shipping_state' => $shippingState,
                    'subTotal' => $preSubtotal,
                    'totalQuantity' => $preQuantity,
                ]
            );
        }

        $rewardEligible = false;
        if ($this->customer?->type) {
            $rewardEligible = in_array(
                $this->customer->type,
                [UserTypeCast::MEMBER, UserTypeCast::PROMOTER],
                true
            );
        }

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->cartable;
            if (! $product instanceof Product) {
                continue;
            }

            $line = CartLineService::make(
                cartService: $this,
                lineItem: $cartItem,
                shippingAddress: $shippingAddress,
                voucherValidator: $voucherValidator,
                rewardEligible: $rewardEligible,
                shippingState: $shippingState
            )->getMeta();

            if (($line['allocated_quantity'] ?? 0) < ($line['requested_quantity'] ?? 0)) {
                $this->meta['warnings'][] = "Only {$line['allocated_quantity']} units available for {$line['product_name']}";
            }

            $subtotal += $line['item_total'];
            $originalSubtotal += $line['summary']['original_sub_total'] ?? $line['item_total'];
            $saleDiscountTotal += $line['summary']['sale_discount'] ?? ($line['summary']['discount'] ?? 0);
            $totalTax += $line['item_tax'];
            $totalBv += $line['bv'];
            $totalPv += $line['pv'];
            $totalRewardPoints += $line['reward_points'];

            $itemBreakdown[] = $line;
            $stockAllocations[$line['product_id']] = $line['stock_entries'];
            foreach (($line['summary']['tax_details'] ?? []) as $detail) {
                $taxBreakdown[] = $detail + [
                    'product_id' => $line['product_id'],
                ];
            }

            $lineMeta[] = [
                'product' => $product,
                'unit_price' => $line['unit_price'],
                'quantity' => $line['requested_quantity'],
                'line_total' => $line['item_total'],
            ];
        }

        $shippingCost = $this->calculateShippingCost($itemBreakdown);
        $discount = 0;
        $voucherDetails = null;
        $voucherValidation = null;
        if ($voucherValidator && $voucherValidator->isValid()) {
            $voucher = $voucherValidator->getVoucher();
            if ($voucher && $voucher->canBeUsed()) {
                $voucherDetails = [
                    'name' => $voucher->name,
                    'code' => $voucher->primaryCode?->code ?? $voucher->codes()->first()?->code,
                    'action_type' => $voucher->action_type?->value,
                    'applies_to_shipping' => $voucher->apply_to_shipping,
                    'free_shipping' => $voucher->free_shipping,
                ];

                if ($voucher->action_type->isCartLevel()) {
                    $baseAmount = $subtotal + $totalTax + ($voucher->apply_to_shipping ? $shippingCost : 0);
                    $discount = $voucher->calculateDiscount($baseAmount);

                    if ($voucher->free_shipping) {
                        $shippingCost = 0;
                    }
                } else {
                    foreach ($lineMeta as $line) {
                        if ($voucher->appliesTo($line['product'])) {
                            $perUnitDiscount = $voucher->calculateItemDiscount($line['unit_price']);
                            $discount += $perUnitDiscount * $line['quantity'];
                        }
                    }
                }
            }
        }
        if ($voucherValidator) {
            $voucherValidation = [
                'valid' => $voucherValidator->isValid(),
                'message' => $voucherValidator->getValidationMessage(),
                'condition_results' => $voucherValidator->getConditionResults(),
                'errors' => $voucherValidator->getConditionErrors(),
            ];
        }

        if ($voucherDetails && ($voucherDetails['free_shipping'] ?? false)) {
            $shippingCost = 0;
        }

        $totalDiscount = $saleDiscountTotal + $discount;
        $total = max(0, $subtotal + $totalTax + $shippingCost - $discount);
        $pointsRate = max(1, (int) config('wallet.points_conversion_rate', 10));
        $coinsRequired = (int) ceil(($total * $pointsRate) / 100);

        return [
            'subtotal' => $subtotal,
            'original_subtotal' => $originalSubtotal,
            'tax' => $totalTax,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'sale_discount' => $saleDiscountTotal,
            'voucher_discount' => $discount,
            'total_discount' => $totalDiscount,
            'total' => $total,
            'bv' => $totalBv,
            'pv' => $totalPv,
            'reward_points' => $totalRewardPoints,
            'total_coins' => $totalRewardPoints,
            'coins_required' => $coinsRequired,
            'item_count' => $cartItems->count(),
            'total_quantity' => $cartItems->sum('quantity'),
            'items' => $itemBreakdown,
            'stock_allocations' => $stockAllocations,
            'tax_breakdown' => $taxBreakdown,
            'voucher_applied' => $discount > 0,
            'voucher_details' => $voucherDetails,
            'voucher_validation' => $voucherValidation,
            'coupon_code' => $this->couponCode,
            'shipping_address_id' => $shippingAddress?->id,
            'formatted' => [
                'subtotal' => MoneyService::format($subtotal),
                'original_subtotal' => MoneyService::format($originalSubtotal),
                'tax' => MoneyService::format($totalTax),
                'shipping_cost' => MoneyService::format($shippingCost),
                'discount' => MoneyService::format($discount),
                'sale_discount' => MoneyService::format($saleDiscountTotal),
                'voucher_discount' => MoneyService::format($discount),
                'total_discount' => MoneyService::format($totalDiscount),
                'total' => MoneyService::format($total),
            ],
        ];
    }

    /**
     * Allocate stock using FIFO (First In First Out)
     * Prioritizes older stock entries and stock closer to shipping address
     */
    protected function allocateStockFifo(Product $product, int $quantity, ?Address $shippingAddress, ?int $overrideUnitPrice = null): array
    {
        $priceService = app(PriceCalculationService::class);
        $context = $priceService->resolveStockContext($shippingAddress);

        $stocks = $product->stocks()
            ->inStock()
            ->with('address')
            ->orderBy('created_at')
            ->get();

        $orderedStocks = $priceService->getOrderedStocksForContext($stocks, $context);

        $allocated = 0;
        $totalPrice = 0;
        $totalBv = 0;
        $totalPv = 0;
        $totalRewardPoints = 0;
        $stockEntries = [];

        foreach ($orderedStocks as $stock) {
            if ($allocated >= $quantity) {
                break;
            }

            $available = $stock->available_stock;
            if ($available <= 0) {
                continue;
            }

            $toAllocate = min($available, $quantity - $allocated);

            $unitPrice = $overrideUnitPrice ?? $product->getPrice();
            $lineTotal = $unitPrice * $toAllocate;

            $stockEntries[] = [
                'stock_id' => $stock->id,
                'quantity' => $toAllocate,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'bv' => $product->bv * $toAllocate,
                'pv' => $product->pv * $toAllocate,
                'reward_points' => $product->reward_points * $toAllocate,
                'warehouse_state' => $stock->address?->state
                    ? Str::lower(trim($stock->address->state->name))
                    : null,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date?->toDateString(),
            ];

            $allocated += $toAllocate;
            $totalPrice += $lineTotal;
            $totalBv += $product->bv * $toAllocate;
            $totalPv += $product->pv * $toAllocate;
            $totalRewardPoints += $product->reward_points * $toAllocate;
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
        $cartTotal = $this->cartCalculation($shippingAddress);

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

    private function calculateShippingCost(array $itemBreakdown): int
    {
        $baseRate = (int) config('shipping.native.base_rate_paise', 0);
        $baseWeight = (int) config('shipping.native.base_weight_grams', 0);
        $ratePerKg = (int) config('shipping.native.rate_per_kg_paise', 0);
        $defaultWeight = (int) config('shipping.native.default_item_weight_grams', 0);

        $totalWeight = 0;
        foreach ($itemBreakdown as $line) {
            $product = $line['product']['instance'] ?? null;
            $weight = is_object($product) ? (int) ($product->weight_grams ?? 0) : 0;
            if ($weight <= 0) {
                $weight = $defaultWeight;
            }
            $totalWeight += $weight * ($line['requested_quantity'] ?? 0);
        }

        if ($totalWeight <= 0) {
            return 0;
        }

        $cost = $baseRate;
        if ($baseWeight > 0 && $totalWeight > $baseWeight) {
            $extraWeight = $totalWeight - $baseWeight;
            $extraUnits = (int) ceil($extraWeight / 1000);
            $cost += $extraUnits * $ratePerKg;
        }

        return max(0, $cost);
    }

    protected function emptyCartTotal(): array
    {
        return [
            'subtotal' => 0,
            'original_subtotal' => 0,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'sale_discount' => 0,
            'voucher_discount' => 0,
            'total_discount' => 0,
            'total' => 0,
            'bv' => 0,
            'pv' => 0,
            'reward_points' => 0,
            'total_coins' => 0,
            'coins_required' => 0,
            'item_count' => 0,
            'total_quantity' => 0,
            'items' => [],
            'stock_allocations' => [],
            'tax_breakdown' => [],
            'coupon_code' => null,
            'shipping_address_id' => null,
            'formatted' => [
                'subtotal' => MoneyService::format(0),
                'original_subtotal' => MoneyService::format(0),
                'tax' => MoneyService::format(0),
                'shipping_cost' => MoneyService::format(0),
                'discount' => MoneyService::format(0),
                'sale_discount' => MoneyService::format(0),
                'voucher_discount' => MoneyService::format(0),
                'total_discount' => MoneyService::format(0),
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

    /**
     * Full cart meta (old_project style)
     */
    public function getMeta(?Address $shippingAddress = null, bool $formatted = false): array
    {
        $summary = $this->cartCalculation($shippingAddress);

        return [
            'summary' => [
                'sub_total' => $summary['subtotal'],
                'original_sub_total' => $summary['original_subtotal'] ?? $summary['subtotal'],
                'shipping_cost' => $summary['shipping_cost'],
                'tax' => $summary['tax'],
                'discount' => $summary['discount'],
                'sale_discount' => $summary['sale_discount'] ?? 0,
                'voucher_discount' => $summary['voucher_discount'] ?? $summary['discount'],
                'total_discount' => $summary['total_discount'] ?? $summary['discount'],
                'coins' => $summary['total_coins'] ?? $summary['reward_points'] ?? 0,
                'coins_required' => $summary['coins_required'] ?? 0,
                'coupon_applied' => $summary['voucher_applied'] ?? false,
                'coupon_code' => $summary['coupon_code'] ?? null,
                'total' => $summary['total'],
                'quantity' => $summary['total_quantity'] ?? 0,
                'formatted' => $summary['formatted'] ?? [],
                'raw' => [
                    'sub_total' => $summary['subtotal'],
                    'original_sub_total' => $summary['original_subtotal'] ?? $summary['subtotal'],
                    'shipping_cost' => $summary['shipping_cost'],
                    'tax' => $summary['tax'],
                    'discount' => $summary['discount'],
                    'sale_discount' => $summary['sale_discount'] ?? 0,
                    'voucher_discount' => $summary['voucher_discount'] ?? $summary['discount'],
                    'total_discount' => $summary['total_discount'] ?? $summary['discount'],
                    'coins' => $summary['total_coins'] ?? $summary['reward_points'] ?? 0,
                    'coins_required' => $summary['coins_required'] ?? 0,
                    'total' => $summary['total'],
                    'quantity' => $summary['total_quantity'] ?? 0,
                ],
            ],
            'customer' => $this->getCustomerMeta(),
            'items' => $summary['items'] ?? [],
            'tax_breakdown' => $summary['tax_breakdown'] ?? [],
            'voucher_details' => $summary['voucher_details'] ?? null,
            'voucher_validation' => $summary['voucher_validation'] ?? null,
            'gift_options' => collect(\App\Casts\GiftOptionCast::cases())
                ->map(fn ($case) => [
                    'value' => $case->value,
                    'label' => $case->getLabel(),
                ])
                ->values()
                ->all(),
            'error' => $this->error,
        ];
    }

    private function getCustomerMeta(): array
    {
        $kyc = $this->customer?->kyc;

        return [
            'identity' => [
                'type' => $this->customer ? 'authenticated' : 'guest',
                'is_guest' => ! $this->customer,
                'token_expires_in' => $this->tokenTTL,
            ],
            'profile' => [
                'name' => $this->customer?->name,
                'email' => $this->customer?->email,
                'mobile' => $this->customer?->mobile,
                'gst_number' => $kyc?->isApproved() ? $kyc->gst_number : null,
                'type' => $this->customer?->type?->value,
                'status_label' => method_exists($this->customer?->status, 'getLabel')
                    ? $this->customer?->status->getLabel()
                    : null,
                'type_label' => method_exists($this->customer?->type, 'getLabel')
                    ? $this->customer?->type->getLabel()
                    : null,
                'class' => $this->customer
                    ? Str::afterLast(get_class($this->customer), '\\')
                    : null,
            ],
        ];
    }
}
