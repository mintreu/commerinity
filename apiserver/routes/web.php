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

Route::get('__testing/sms/dlt-single', function () {
    return response()->json([
        'message' => 'Use POST /__testing/sms/dlt-single to send debug SMS.',
    ], 405);
});

Route::post('__testing/sms/dlt-single', [\App\Http\Controllers\DebugSmsController::class, 'testDltSingle']);
