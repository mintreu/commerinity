<?php

namespace Mintreu\LaravelCommerinity\Services\CartService;

use App\Models\Cart as CartModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelCommerinity\Models\SaleProduct;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;

class CartLineService
{

    protected CartService $cartService;
    protected CartModel $lineItem;   // actual cart line (row in cart)
    protected Model $cartable;       // morphable (Product, Service, etc.)
    protected ?CartVoucherValidator $voucherValidator = null;
    protected ?Collection $sales = null;
    protected ?ProductTier $cheapestTire = null;
    protected int $resolveProductPrice = 0;
    protected ?SaleProduct $applicableSale = null;
    protected CartSaleValidator $cartSaleValidator;
    protected LaravelMoney $subTotal;
    protected LaravelMoney $discount;
    protected LaravelMoney $taxAmount;
    protected LaravelMoney $total;

    public function __construct(CartService $cartService,CartModel $lineItem,?CartVoucherValidator $voucherValidator = null)
    {
        $this->subTotal = LaravelMoney::make(0);
        $this->discount = LaravelMoney::make(0);
        $this->taxAmount = LaravelMoney::make(0);
        $this->total = LaravelMoney::make(0);

        $this->cartService = $cartService;
        $this->lineItem = $lineItem;
        $this->voucherValidator = $voucherValidator;
//        $this->lineItem->loadMissing(['cartable']);

        $this->cartable = $this->lineItem->cartable;

        $this->cheapestTire = $this->cartable?->cheapestTier;
        $this->resolveProductPrice = !is_null($this->cheapestTire) ? $this->cheapestTire->price : $this->cartable->price;

        $this->cartSaleValidator = CartSaleValidator::make($cartService,$this->lineItem)
            ->setResolvedPrice($this->resolveProductPrice);





    }

    public static function make(CartService $cartService, CartModel $lineItem,?CartVoucherValidator $voucherValidator = null): static
    {
        return new static($cartService,$lineItem,$voucherValidator);
    }



    public function getMeta(bool $formatted)
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
                'quantity'  => $this->lineItem->quantity,
                'original_price' => LaravelMoney::format($this->resolveProductPrice),
                'discounted_price' => $this->discount?->formatted() ?? LaravelMoney::format($this->resolveProductPrice),
                'sub_total' => $this->subTotal->formatted(),
//                'discount'  => $this->applicableSale?->action_type?->getUnit($this->applicableSale?->discount_amount),
                'discount'  => $this->applicableSale?->action_type?->getUnit($this->discount),
                'voucher'   => $this->voucherValidator->getCoupon(),
                'valid_voucher' => $this->voucherValidator->isValid(),
                'tax'       => null,
                'total'     => $this->total->formatted(),

                'raw' => [
                    'sub_total' => $this->subTotal,
                    'discount'  => $this->discount,
                    'tax'       => $this->taxAmount,
                    'total'     => $this->total,
                ]
            ],
            'tire' => $this->cheapestTire,
            'errors' => $this->cartService->getErrors()
        ],$this->cartSaleValidator->toArray());
    }


    public function calculating()
    {
        $validSale = $this->cartSaleValidator->validate();

        $validVoucher = $this->voucherValidator->validate($this->cartable);


        $this->subTotal = $this->subTotal->add($this->resolveProductPrice)->multiply($this->lineItem->quantity);


        $this->total = $this->total->add($this->subTotal)->subtract($this->discount)->add($this->taxAmount);



    }







}
