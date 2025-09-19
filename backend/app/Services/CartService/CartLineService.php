<?php

namespace App\Services\CartService;

use \App\Models\Cart as CartModel;
use Illuminate\Database\Eloquent\Model;

class CartLineService
{

    protected CartModel $lineItem;   // actual cart line (row in cart)
    protected Model $cartable;       // morphable (Product, Service, etc.)

    public function __construct(CartModel $lineItem)
    {
        $this->lineItem = $lineItem;
        $this->lineItem->load(['cartable']);

        $this->cartable = $this->lineItem->cartable;
    }

    public static function make(CartModel $lineItem): static
    {
        return new static($lineItem);
    }

    public function getMeta(bool $formatted)
    {
    }


}
