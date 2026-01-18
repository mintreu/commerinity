<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\WishlistItemResource;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductWishlist;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;

/**
 * Wishlist API Controller
 *
 * Handles user's wishlist/favorites functionality
 */
final class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $wishlist = ProductWishlist::query()
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            // with() optimizations same as before
            ->with([
                'product' => function ($q) {
                    $q->with([
                        'media' => fn ($mq) => $mq->where('collection_name', 'displayImage'),
                        'category',
                    ])
                        ->withStockInfo();
                },
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => WishlistItemResource::collection($wishlist->getCollection()),
                'pagination' => [
                    'current_page' => $wishlist->currentPage(),
                    'last_page' => $wishlist->lastPage(),
                    'per_page' => $wishlist->perPage(),
                    'total' => $wishlist->total(),
                    'has_more' => $wishlist->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function store(Product $product): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check if already in wishlist
        $exists = ProductWishlist::query()
            ->where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist',
            ], 422);
        }

        ProductWishlist::create([
            'product_id' => $product->id,
            'authorable_id' => $user->id,
            'authorable_type' => User::class,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => [
                'in_wishlist' => true,
            ],
        ], 201);
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Product $product): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $deleted = ProductWishlist::query()
            ->where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not in wishlist',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
            'data' => [
                'in_wishlist' => false,
            ],
        ]);
    }

    /**
     * Toggle wishlist status (add/remove)
     */
    public function toggle(Product $product): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $existing = ProductWishlist::query()
            ->where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist',
                'data' => [
                    'in_wishlist' => false,
                ],
            ]);
        }

        ProductWishlist::create([
            'product_id' => $product->id,
            'authorable_id' => $user->id,
            'authorable_type' => User::class,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => [
                'in_wishlist' => true,
            ],
        ]);
    }

    /**
     * Check if a product is in user's wishlist
     */
    public function check(Product $product): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => false,
                ],
            ]);
        }

        $inWishlist = ProductWishlist::query()
            ->where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $inWishlist,
            ],
        ]);
    }
}
