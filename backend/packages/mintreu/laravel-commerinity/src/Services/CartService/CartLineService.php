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
    protected null|int|LaravelMoney $discount = null;
    protected ?SaleProduct $applicableSale = null;
    protected CartSaleValidator $cartSaleValidator;

    public function __construct(CartService $cartService,CartModel $lineItem,?CartVoucherValidator $voucherValidator = null)
    {
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
            ],
            'summary' => [
                'quantity'  => null,
                'original_price' => LaravelMoney::format($this->resolveProductPrice),
                'discounted_price' => $this->discount?->formatted() ?? LaravelMoney::format($this->resolveProductPrice),
                'sub_total' => LaravelMoney::format($this->discount?->getAmount() ?? $this->resolveProductPrice),
                'discount'  => $this->applicableSale?->action_type?->getUnit($this->applicableSale?->discount_amount),
                'voucher'   => $this->voucherValidator->getCoupon(),
                'valid_voucher' => $this->voucherValidator->isValid(),
                'tax'       => null,
                'total'     => LaravelMoney::make($this->discount?->getAmount() ?? $this->resolveProductPrice)->formatted(),
            ],
            'tire' => $this->cheapestTire,
            'errors' => $this->cartService->getErrors()
        ],$this->cartSaleValidator->toArray());
    }


    public function calculating()
    {

        if ($this->cartSaleValidator->validate() && $this->voucherValidator->validate($this->cartable))
        {
            //
        }


    }







}
