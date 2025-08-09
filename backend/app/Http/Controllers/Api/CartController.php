<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Services\CartService\Cart;
use Illuminate\Http\Request;
use Mintreu\LaravelProductCatalogue\Models\Product;

class CartController extends Controller
{


    public function ensureGuestCartCredential(Request $request)
    {
        $cart = new Cart($request->user());
        $cart->capture($request);
        return $cart->ensureGuestCredential();
    }



    // 1. Get Cart
    public function index(Request $request)
    {
        $cart = new Cart($request->user());
        if (!$request->user())
        {
            $cart->capture($request);
        }

        return CartResource::make($cart->getMeta());
    }

    // 2. Add Product
    public function addProduct(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);

        $cart = new Cart($request->user());
        $cart->capture($request);
        $cart->add($product,$quantity);
        return CartResource::make($cart->getMeta());
    }

    public function updateProduct(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);

        $cart = new Cart($request->user());
        $cart->capture($request);
        $cart->update($product,$quantity);
        return CartResource::make($cart->getMeta());
    }






    // 3. Remove Product
    public function removeProduct(Request $request, Product $product)
    {
        $cart = new Cart($request->user());
        $cart->capture($request);
        $cart->delete($product);

        return CartResource::make($cart->getMeta());
    }

    // 4. Clear Entire Cart
    public function clearCart(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->user()) {
            $request->user()->cartItems()->delete();
            return response()->json(['message' => 'User cart cleared']);
        }

        session()->forget('guest_cart');
        return response()->json(['message' => 'Guest cart cleared']);
    }

    // 5. Merge Guest Cart After Login/Register
    public function mergeGuestCart(Request $request): \Illuminate\Http\JsonResponse
    {
        $guestCart = session('guest_cart', []);
        $user = $request->user();

        foreach ($guestCart as $item) {
            $product = Product::where('sku', $item['sku'])->first();
            if (!$product) continue;

            $cartItem = $user->cartItems()->firstOrNew(['product_id' => $product->id]);
            $cartItem->quantity += $item['quantity'];
            $cartItem->save();
        }

        session()->forget('guest_cart');

        return response()->json(['message' => 'Guest cart merged successfully']);
    }
}
