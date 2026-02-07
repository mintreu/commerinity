<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService;

use App\Models\Address;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Product;
use App\Services\Ecommerce\PriceCalculationService;
use App\Casts\GstTaxCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CartLineService
{
    protected CartService $cartService;
    protected Cart $lineItem;
    protected Product $product;
    protected ?Address $shippingAddress;
    protected ?CartVoucherValidator $voucherValidator;
    protected bool $rewardEligible;
    protected ?string $shippingState;

    public function __construct(
        CartService $cartService,
        Cart $lineItem,
        ?Address $shippingAddress = null,
        ?CartVoucherValidator $voucherValidator = null,
        bool $rewardEligible = false,
        ?string $shippingState = null
    ) {
        $this->cartService = $cartService;
        $this->lineItem = $lineItem;
        $this->shippingAddress = $shippingAddress;
        $this->voucherValidator = $voucherValidator;
        $this->rewardEligible = $rewardEligible;
        $this->shippingState = $shippingState;
        $this->product = $this->lineItem->cartable;
    }

    public static function make(
        CartService $cartService,
        Cart $lineItem,
        ?Address $shippingAddress = null,
        ?CartVoucherValidator $voucherValidator = null,
        bool $rewardEligible = false,
        ?string $shippingState = null
    ): self {
        return new self($cartService, $lineItem, $shippingAddress, $voucherValidator, $rewardEligible, $shippingState);
    }

    public function getMeta(): array
    {
        if (! $this->product instanceof Product) {
            return [];
        }

        $quantity = $this->lineItem->quantity;

        $originalPrice = $this->product->getPrice();
        $saleValidator = CartSaleValidator::make($this->cartService, $this->lineItem)
            ->setResolvedPrice($originalPrice);
        $saleValidator->validate();
        $salePrice = $saleValidator->getResolvedPrice();

        $allocation = $this->allocateStockFifo($this->product, $quantity, $this->shippingAddress, $salePrice);

        $itemTotal = $allocation['total_price'];
        $originalLineTotal = $originalPrice * $quantity;
        $taxDetails = [];
        $itemTax = $this->calculateTax($allocation['stock_entries'], $taxDetails);
        $lineDiscount = max(0, ($originalPrice - $salePrice) * $quantity);

        $lineBv = $this->rewardEligible ? $allocation['total_bv'] : 0;
        $linePv = $this->rewardEligible ? $allocation['total_pv'] : 0;
        $lineReward = $allocation['total_reward_points'];

        if ($this->voucherValidator) {
            $this->voucherValidator->validate($this->product);
        }

        return [
            'cart_id' => $this->lineItem->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'requested_quantity' => $quantity,
            'allocated_quantity' => $allocation['allocated_quantity'],
            'unit_price' => $salePrice,
            'item_total' => $itemTotal,
            'item_tax' => $itemTax,
            'bv' => $lineBv,
            'pv' => $linePv,
            'reward_points' => $lineReward,
            'stock_entries' => $allocation['stock_entries'],
            'sale_info' => $saleValidator->toArray(),
            'product' => [
                'name' => $this->product->name,
                'url' => $this->product->url,
                'sku' => $this->product->sku,
                'type' => $this->product->type?->value,
                'min_quantity' => $this->product->min_quantity,
                'max_quantity' => $this->product->max_quantity,
                'price' => $originalPrice,
                'thumbnail' => method_exists($this->product, 'getFirstMediaUrl')
                    ? $this->product->getFirstMediaUrl('displayImage')
                    : null,
                'instance' => $this->product,
            ],
            'summary' => [
                'quantity' => $quantity,
                'original_price' => $originalPrice,
                'discounted_price' => $salePrice,
                'sub_total' => $itemTotal,
                'original_sub_total' => $originalLineTotal,
                'discount' => $lineDiscount,
                'sale_discount' => $lineDiscount,
                'voucher' => $this->voucherValidator?->getCoupon(),
                'valid_voucher' => $this->voucherValidator?->isValid() ?? false,
                'tax' => $itemTax,
                'tax_details' => $taxDetails,
                'shipping_cost' => 0,
                'total' => $itemTotal + $itemTax,
                'raw' => [
                    'sub_total' => $itemTotal,
                    'original_sub_total' => $originalLineTotal,
                    'discount' => $lineDiscount,
                    'sale_discount' => $lineDiscount,
                    'tax' => $itemTax,
                    'shipping_cost' => 0,
                    'total' => $itemTotal + $itemTax,
                ],
            ],
        ];
    }

    private function calculateTax(array $stockEntries, array &$taxDetails): int
    {
        $itemTax = 0;
        $gst = $this->product->gst_tax_type
            ?? $this->product->category?->tax_slab;

        $gstPercentage = 0;
        if ($gst instanceof GstTaxCast) {
            $gstPercentage = $gst->percentage();
        } elseif (is_string($gst)) {
            $gstPercentage = GstTaxCast::tryFrom($gst)?->percentage() ?? 0;
        }

        if ($gstPercentage <= 0 || ! $this->shippingState) {
            return 0;
        }

        foreach ($stockEntries as $entry) {
            $warehouseState = $entry['warehouse_state'] ?? null;
            if ($warehouseState) {
                $taxAmount = (int) round($entry['line_total'] * $gstPercentage / 100);
                $itemTax += $taxAmount;
                $taxType = \App\Casts\GstTaxCast::determineTaxType($this->shippingState, $warehouseState);
                $taxDetails[] = [
                    'gst_type' => $taxType->value,
                    'gst_percentage' => $gstPercentage,
                    'warehouse_state' => $warehouseState,
                    'shipping_state' => $this->shippingState,
                    'tax_amount' => $taxAmount,
                ];
            }
        }

        return $itemTax;
    }

    private function allocateStockFifo(Product $product, int $quantity, ?Address $shippingAddress, ?int $overrideUnitPrice = null): array
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
}
