<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mintreu\LaravelProductCatalogue\Models\Product;

class ProductWishlistController extends Controller
{






    public function addWishlist(Product $product,Request $request)
    {
        $user = $request->user();
        $user->addToWishlist($product->id);
        return response()->json([
            'success' => true,
            'message' => $product->name.' successfully added in your wishlist'
        ]);
    }

    public function removeWishlist(Product $product,Request $request)
    {
        $user = $request->user();
        $user->removeWishlist($product->id);
        return response()->json([
            'success' => true,
            'message' => $product->name.' successfully removed from in your wishlist'
        ]);
    }





}
