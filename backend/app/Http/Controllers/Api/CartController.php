<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // 1. Get Cart
    public function index(Request $request)
    {
        if ($request->user()) {
            $items = $request->user()->cartItems()->with('product')->get();
            return response()->json($items);
        }

        return response()->json(session('guest_cart', []));
    }

    // 2. Add Product
    public function addProduct(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);

        if ($request->user()) {
            $user = $request->user();
            $item = $user->cartItems()->firstOrNew(['product_id' => $product->id]);
            $item->quantity += $quantity;
            $item->save();

            return response()->json(['message' => 'Product added to user cart']);
        }

        $cart = session()->get('guest_cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['sku'] === $product->sku) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = ['sku' => $product->sku, 'name' => $product->name, 'price' => $product->price, 'quantity' => $quantity];
        }

        session(['guest_cart' => $cart]);

        return response()->json(['message' => 'Product added to guest cart']);
    }

    // 3. Remove Product
    public function removeProduct(Request $request, Product $product)
    {
        if ($request->user()) {
            $request->user()->cartItems()->where('product_id', $product->id)->delete();
            return response()->json(['message' => 'Product removed from user cart']);
        }

        $cart = session()->get('guest_cart', []);
        $cart = array_filter($cart, fn($item) => $item['sku'] !== $product->sku);
        session(['guest_cart' => array_values($cart)]);

        return response()->json(['message' => 'Product removed from guest cart']);
    }

    // 4. Clear Entire Cart
    public function clearCart(Request $request)
    {
        if ($request->user()) {
            $request->user()->cartItems()->delete();
            return response()->json(['message' => 'User cart cleared']);
        }

        session()->forget('guest_cart');
        return response()->json(['message' => 'Guest cart cleared']);
    }

    // 5. Merge Guest Cart After Login/Register
    public function mergeGuestCart(Request $request)
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
