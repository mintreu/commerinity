<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Prefix: products

Route::controller(\App\Http\Controllers\Api\Product\ProductDisplayController::class)
    ->prefix('products')
    ->group(function (){
       Route::get('/','index');
    });


