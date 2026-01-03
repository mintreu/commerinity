<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



/**
 * CHECKOUT ROUTES
 */

Route::get('/checkout/{transaction:uuid}',\App\Livewire\Checkout\CheckoutHome::class)->name('checkout');




/**
 * Debug Test Controller
 */

Route::get('__testing',[\App\Http\Controllers\DebugAuthController::class,'index']);
