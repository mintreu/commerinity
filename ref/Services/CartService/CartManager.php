<?php

namespace App\Services\CartService;

use App\Models\Cart\Cart;
use App\Models\Store\Product\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CartManager
{

    protected bool $changed = false;
    protected array $errors = [];
    public bool $requestForReLoadCustomerCartInRuntime = false;
    protected Model $customer;
    protected Product $product;
    protected array $meta = [];
    protected int $totalQuantity = 0;
    protected ?string $couponCode = null;
    protected bool $validCoupon = false;


    /**
     * @param Model $customer
     */
    public function __construct(Model $customer)
    {
        abort_unless($customer instanceof Authenticatable,404);
        $this->customer = $customer;
        if (is_null($this->customer?->cart))
        {
            $this->customer->loadMissing('cart','cart.product');
        }


    }


    public static function make(Authenticatable|Model $customer): static
    {
        return new static($customer);
    }





    public function getCustomer():Model|Authenticatable
    {
        return $this->customer;
    }

    public function items(): Collection
    {
        if (App::runningInConsole() || $this->requestForReLoadCustomerCartInRuntime) {
            return  $this->customer->fresh()->cart;
        }
        return $this->customer->cart;
    }

    public function getTotalQuantity(): int
    {
        $this->totalQuantity = $this->items()->sum('quantity');
        return $this->totalQuantity;
    }

    public function empty(): void
    {
        $this->customer->cart->map(function (Cart $cart){
            $cart->delete();
        });

       // $this->customer->cart()->detach();
    }

    public function isEmpty(): bool
    {
        return $this->customer->cart->sum('quantity') === 0;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }

    public function setError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }


    /**
     * CURD OPERATIONS
     */

    public function add(Model|Product $item, int $quantity): void
    {
        abort_unless($item instanceof Product, 404);

        $maxAllowed = $item->max_per_order;
        $minAllowed = $item->min_per_order;

        $existItem = $this->customer->cart->firstWhere('product_id', $item->id);

        if ($existItem) {
            // Update the existing item directly
            $this->update($existItem, $existItem->quantity + $quantity);
        } else {
            // Ensure quantity is within the allowed range
            $quantity = max($minAllowed, min($quantity, $maxAllowed));

            // Add new product to the cart
            $this->customer->cart()->create([
                'product_id' => $item->id,
                'quantity' => $quantity
            ]);
        }
    }





    public function update(Cart $cartItem, int $quantity): void
    {
        $item = $cartItem->product;

        // Get the allowed quantity range from the associated product
        $maxAllowed = $item->max_per_order ?? 1; // Default max to 1 if null
        $minAllowed = $item->min_per_order ?? 1; // Default min to 1 if null

        // Ensure the quantity is within the allowed range
        $approvedQuantity = max($minAllowed, min($quantity, $maxAllowed));

        // Update the cart entry with the approved quantity
        $cartItem->update(['quantity' => $approvedQuantity]);
    }



    public function delete(Model|Cart $item): void
    {
        $item->delete();
    }




    /**
     * CURD OPERATIONS On Coupons
     */



}
