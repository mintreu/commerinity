<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Prefix: products

Route::get('/',[\App\Http\Controllers\Api\Product\ProductDisplayController::class,'index'])->name('api.product.index');

