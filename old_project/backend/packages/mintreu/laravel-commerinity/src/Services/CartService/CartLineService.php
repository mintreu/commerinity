<?php

namespace Mintreu\LaravelCommerinity\Services\CartService;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCommerinity\Models\SaleProduct;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;
use Mintreu\LaravelProductCatalogue\Services\StockLocatorService;
use App\Services\TaxCalculationService;
use App\Models\Cart as CartModel;

class CartLineService
{

    protected CartService $cartService;
    protected CartModel $lineItem;   // actual cart line (row in cart)
    protected Model $cartable;       // morphable (Product, Service, etc.)
    protected ?Address $customerAddress;
    protected ?CartVoucherValidator $voucherValidator = null;
    protected ?Collection $sales = null;
    protected ?ProductTier $bestTier = null;
    protected int $resolveProductPrice = 0;
    protected ?SaleProduct $applicableSale = null;
    protected CartSaleValidator $cartSaleValidator;
    protected LaravelMoney $subTotal;
    protected LaravelMoney $discount;
    protected LaravelMoney $taxAmount;
    protected array $taxDetails = [];
    protected LaravelMoney $total;

    public function __construct(CartService $cartService, CartModel $lineItem, ?Address $customerAddress, ?CartVoucherValidator $voucherValidator = null)
    {
        $this->subTotal = LaravelMoney::make(0);
        $this->discount = LaravelMoney::make(0);
        $this->taxAmount = LaravelMoney::make(0);
        $this->total = LaravelMoney::make(0);
        $this->taxDetails = [];

        $this->cartService = $cartService;
        $this->lineItem = $lineItem;
        $this->customerAddress = $customerAddress;
        $this->voucherValidator = $voucherValidator;

        $this->cartable = $this->lineItem->cartable;

        // New Location-Aware Logic
        if ($this->customerAddress) {
            $stockLocator = new StockLocatorService();
            $this->bestTier = $stockLocator->find($this->cartable, $this->customerAddress);
        }

        // Validate stock and set price
        if ($this->bestTier) {
            if ($this->bestTier->available_stock < $this->lineItem->quantity) {
                $this->cartService->setError("Only {$this->bestTier->available_stock} items left in stock for {$this->cartable->name}.");
            }
            $this->resolveProductPrice = $this->bestTier->price;
        } else {
            // Only set error if an address was provided, meaning a location-specific search was attempted and failed
            if ($this->customerAddress) {
                 $this->cartService->setError("{$this->cartable->name} is not available for your selected address.");
            }
            $this->resolveProductPrice = $this->cartable->price; // Fallback to base price
        }


        $this->cartSaleValidator = CartSaleValidator::make($cartService, $this->lineItem)
            ->setResolvedPrice($this->resolveProductPrice);
    }

    public static function make(CartService $cartService, CartModel $lineItem, ?Address $customerAddress, ?CartVoucherValidator $voucherValidator = null): static
    {
        return new static($cartService, $lineItem, $customerAddress, $voucherValidator);
    }



    public function getMeta(bool $formatted = false)
    {
        $this->calculating();


        return array_merge([
            'product_id' => $this->cartable->id,
            'quantity'   => $this->lineItem->quantity,

            'product'    => [
                'name'         => $this->cartable->name,
                'url'          => $this->cartable->url,
                'sku'          => $this->cartable->sku,
                'type'         => $this->cartable->type->value,
                'min_quantity' => $this->cartable->min_quantity,
                'max_quantity' => $this->cartable->max_quantity,
                'price'        => $this->resolveProductPrice,
                'thumbnail'    => $this->cartable?->getFirstMediaUrl('displayImage'),
                'instance'     => $this->cartable,
            ],
            'summary' => [
                'quantity'          => $this->lineItem->quantity,
                'original_price'    => LaravelMoney::format($this->resolveProductPrice),
                'discounted_price'  => $this->discount?->formatted() ?? LaravelMoney::format($this->resolveProductPrice),
                'sub_total'         => $this->subTotal->formatted(),
//                'discount'  => $this->applicableSale?->action_type?->getUnit($this->applicableSale?->discount_amount),
                'discount'          => $this->applicableSale?->action_type?->getUnit($this->discount),
                'voucher'           => $this->voucherValidator->getCoupon(),
                'valid_voucher'     => $this->voucherValidator->isValid(),
                'tax'               => $this->taxAmount->formatted(),
                'tax_details'       => $this->taxDetails,
                'shipping_cost'     => LaravelMoney::format(0),
                'total'             => $this->total->formatted(),

                'raw' => [
                    'sub_total'         => $formatted ? $this->subTotal->getAmount() : $this->subTotal,
                    'discount'          => $formatted ? $this->discount->getAmount() : $this->discount,
                    'tax'               => $formatted ? $this->taxAmount->getAmount() : $this->taxAmount,
                    'shipping_cost'     => $formatted ? LaravelMoney::format(0) :LaravelMoney::make(0),
                    'total'             => $formatted ? $this->total->getAmount() : $this->total,
                ]
            ],
            'tier' => $this->bestTier,
            'errors' => $this->cartService->getErrors()
        ],$this->cartSaleValidator->toArray());
    }


    public function calculating()
    {
        $validSale = $this->cartSaleValidator->validate();
        $validVoucher = $this->voucherValidator->validate($this->cartable);

        $this->subTotal = LaravelMoney::make($this->resolveProductPrice)->multiply($this->lineItem->quantity);

        // Calculate Tax based on location
        $sellerAddress = $this->bestTier?->address;
        if ($this->customerAddress && $sellerAddress) {
            $taxService = new TaxCalculationService();
            $this->taxDetails = $taxService->calculate($this->cartable, $this->subTotal, $sellerAddress, $this->customerAddress);
            $this->taxAmount = $this->taxDetails['total_tax'];
        }

        // Final total calculation
        $this->total = $this->subTotal->subtract($this->discount)->add($this->taxAmount);
    }







}
