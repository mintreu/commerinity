<?php

namespace App\Filament\Resources\Order\OrderResource\Schemas\Traits;


use App\Models\Product;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mintreu\LaravelCommerinity\Services\CartService\Cart;
use Mintreu\LaravelCommerinity\Services\CartService\CartService;

trait HasOrderFormSupport
{


    public static function getProducts(?int $productId = null, bool $force = false)
    {
        $allProducts = Cache::remember('published_products_with_media', 300, function () {
//            return Product::with(['availableStocks'])
//                ->where('status', 'Published')
//                ->get();

            return Product::where('status', 'Published')
                ->get();
        });

        if ($force && $productId) {
            return $allProducts->find($productId) ?? null;
        }

        return $productId ? $allProducts->find($productId) : $allProducts;
    }







    public static function updateCustomerCart($state,Set $set,array|Collection $newCartDetails = [])
    {
        $customer = User::with([
            'addresses' => fn($query) => $query->where('default', true),
            'cart',
        ])->find($state);


        if ($customer)
        {
            // Cached it
            $set('cached_customer', $customer);

            // Map customer’s cart items into order form structure
            $cartProducts = $customer->cart->map(function ($item) {
                return [
                    'cartable_id' => $item->cartable_id,
                    'quantity' => $item->quantity,
                ];
            })->filter()->toArray();
            // Set Customer Cart (Repeater)
            $set('cart', $cartProducts);

            // Set Address (Select Fields)
            $customerAddress = $customer->addresses->pluck('id')->toArray();
            $set('shipping_address_id',$customerAddress);
            $set('billing_address_id',$customerAddress);


        }
        return null;
    }


    protected static function resolveLiveCart(Get $get,Set $set)
    {
        $formCart = collect(array_values($get('cart')));
        $customer = $get('cached_customer');


        $selectedProducts = $formCart->map(function ($item) {
            return isset($item['cartable_id']) ? self::getProducts($item['cartable_id'],true) : null;
        })->filter();

        $cart = new Cart($customer);

        if ($selectedProducts)
        {
            $cart->empty($customer);
            //Fill With Fresh
            $selectedProducts->each(function ($item) use($cart,$customer,$formCart){
                $formItem = $formCart->where('cartable_id',$item->id)->first();
                $cart->add($item,$formItem['quantity']);
            });
        }
        $meta = $cart->getMeta(formatted: true);




        $summary = $meta['summary'];
        // Save in livewire cache
        $set('cached_meta',array_merge($summary,$meta['items']));




//        $subTotal = $formCart->sum('hidden_total');
        $set('subtotal',$summary['sub_total'] ?? 0);
        $set('shipping_cost',$summary['shipping_cost'] ?? 0);
        $set('tax',$summary['tax'] ?? 0);
        $set('discount',$summary['discount'] ?? 0);
        $set('total',$summary['total'] ?? 0);
    }



}
