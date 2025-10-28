<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Prefix: products

Route::prefix('products')->group(function (){
    // 1. List route (exact match)
    Route::get('/', [\App\Http\Controllers\Api\Product\ProductListController::class, 'index']);

    // 2. Filter/Sort routes (exact match)
    Route::get('filters/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getFilterOptions']);
    Route::get('sorts/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getSortingOptions']);

    // 3. Quick Get routes (exact match) - MOVE THESE UP ⬆️
    Route::get('bestSaleProducts', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'bestSaleProducts']);
    Route::get('trendingProducts', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'trendingProducts']);

    // 4. Product Show (wildcard) - KEEP THIS LAST ⬇️
    Route::get('{product:url}', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'show']);
});


