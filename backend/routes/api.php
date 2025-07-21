<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//Token Based
Route::post('/tokens/create',[\App\Http\Controllers\Auth\AuthController::class,'storeToken'])->middleware(['guest']);

Route::post('/tokens/delete',[\App\Http\Controllers\Auth\AuthController::class,'destroyToken'])->middleware(['auth:sanctum']);


Route::post('login',[\App\Http\Controllers\Auth\AuthController::class,'login']);
Route::post('logout',[\App\Http\Controllers\Auth\AuthController::class,'logout']);


Route::prefix('categories')->group(function (){
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/with-products', [CategoryController::class, 'getParentCategoriesWithProducts']);
    Route::get('{category:url}', [CategoryController::class, 'show']);
});


Route::prefix('/products')->group(function (){
    Route::get('filters/get', [\App\Http\Controllers\Api\ProductController::class, 'getFilterOptions']);
    Route::get('sorts/get', [\App\Http\Controllers\Api\ProductController::class, 'getSortingOptions']);
    Route::get('{product:url}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

});
