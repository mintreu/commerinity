<?php

use App\Http\Controllers\WebPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('{url}', [WebPageController::class, 'show'])->name('web.pages.show');




Route::get('/test',[\App\Http\Controllers\TestController::class,'index']);


/**
 * CHECKOUT ROUTES
 */

Route::get('/checkout/{transaction:uuid}',\App\Livewire\Checkout\CheckoutHome::class)->name('checkout');
