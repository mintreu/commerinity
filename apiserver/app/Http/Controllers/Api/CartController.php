<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\Address;
use App\Models\Ecommerce\Product;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService(auth('sanctum')->user());
    }

    /**
     * Get guest credentials for cart
     */
    public function guestCredential(Request $request): JsonResponse
    {
        $this->cartService->capture($request);

        return $this->cartService->ensureGuestCredential();
    }

    /**
     * Get cart items
     */
    public function index(Request $request): JsonResponse
    {
        $this->cartService->capture($request);

        $shippingAddress = $this->resolveShippingAddress($request);
        if ($shippingAddress) {
            $request->attributes->set('stock_context', $shippingAddress);
        }

        $items = $this->cartService->items();

        if ($this->cartService->hasErrors()) {
            return response()->json([
                'success' => false,
                'message' => $this->cartService->getErrors(),
            ], 400);
        }

        $cartItems = CartItemResource::collection($items);
        $cartTotal = $this->cartService->getCartTotal($shippingAddress);
        $subtotal = $cartTotal['subtotal'] ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'summary' => [
                    'items_count' => $this->cartService->count(),
                    'total_quantity' => $this->cartService->getTotalQuantity(),
                    'subtotal' => $subtotal,
                    'subtotal_formatted' => $cartTotal['formatted']['subtotal'] ?? MoneyService::format($subtotal),
                ],
                'is_guest' => $this->cartService->isGuest(),
            ],
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_slug' => 'required|string|exists:products,url',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $this->cartService->capture($request);

        $product = Product::where('url', $request->product_slug)->firstOrFail();

        // Check if product is purchasable
        if (! $product->status->isPurchasable()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available for purchase.',
            ], 400);
        }

        $this->cartService->add($product, $request->quantity);

        if ($this->cartService->hasErrors()) {
            return response()->json([
                'success' => false,
                'message' => $this->cartService->getErrors(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'data' => [
                'items_count' => $this->cartService->count(),
                'total_quantity' => $this->cartService->getTotalQuantity(),
            ],
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, string $productSlug): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $this->cartService->capture($request);

        $product = Product::where('url', $productSlug)->firstOrFail();

        $this->cartService->update($product, $request->quantity);

        if ($this->cartService->hasErrors()) {
            return response()->json([
                'success' => false,
                'message' => $this->cartService->getErrors(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'data' => [
                'items_count' => $this->cartService->count(),
                'total_quantity' => $this->cartService->getTotalQuantity(),
            ],
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, string $productSlug): JsonResponse
    {
        $this->cartService->capture($request);

        $product = Product::where('url', $productSlug)->firstOrFail();

        $this->cartService->delete($product);

        if ($this->cartService->hasErrors()) {
            return response()->json([
                'success' => false,
                'message' => $this->cartService->getErrors(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data' => [
                'items_count' => $this->cartService->count(),
                'total_quantity' => $this->cartService->getTotalQuantity(),
            ],
        ]);
    }

    /**
     * Empty cart
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->capture($request);

        $this->cartService->empty();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * Get cart count (for header badge)
     */
    public function count(Request $request): JsonResponse
    {
        $this->cartService->capture($request);

        return response()->json([
            'success' => true,
            'data' => [
                'items_count' => $this->cartService->count(),
                'total_quantity' => $this->cartService->getTotalQuantity(),
            ],
        ]);
    }

    private function resolveShippingAddress(Request $request): ?Address
    {
        $user = auth('sanctum')->user();

        if (! $user || ! $request->filled('shipping_address_id')) {
            return null;
        }

        return $user->addresses()
            ->where('uuid', $request->input('shipping_address_id'))
            ->first();
    }
}
