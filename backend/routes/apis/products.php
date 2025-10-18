<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Prefix: products

Route::prefix('products')->group(function (){
    Route::get('/',[\App\Http\Controllers\Api\Product\ProductListController::class,'index']);
    Route::get('filters/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getFilterOptions']); // GET /products/filters/get
    Route::get('sorts/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getSortingOptions']); // GET /products/sorts/get

    // Product Show
    Route::get('{product:url}',[\App\Http\Controllers\Api\Product\ProductDisplayController::class,'show']);


    // Quick Gets
    Route::get('bestSaleProducts',[\App\Http\Controllers\Api\Product\ProductDisplayController::class,'topSaleProduct']);
    Route::get('trendingProducts',[\App\Http\Controllers\Api\Product\ProductDisplayController::class,'trendingProducts']);





});

