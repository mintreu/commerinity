<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
    //return UserResource::make();
})->middleware('auth:sanctum');


//Token Based [ future plan for tenant type]

Route::post('/tokens/create',[AuthController::class,'storeToken'])->middleware(['guest']);

Route::post('/tokens/delete',[AuthController::class,'destroyToken'])->middleware(['auth:sanctum']);

// sanctum cookie based [currently used]

Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout']);
Route::post('/auth/has_contact',[AuthController::class,'checkContactExistence']);
Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);
Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);
Route::post('register',[AuthController::class,'register']);
Route::post('reset_password',[AuthController::class,'resetPassword']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/password', [AuthController::class, 'updatePassword']);
});


Route::prefix('categories')->group(function (){
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/with-products', [CategoryController::class, 'getParentCategoriesWithProducts']);
    Route::get('{category:url}', [CategoryController::class, 'show']);
});


Route::prefix('/products')->group(function (){
    Route::get('filters/get', [\App\Http\Controllers\Api\ProductController::class, 'getFilterOptions']);
    Route::get('sorts/get', [\App\Http\Controllers\Api\ProductController::class, 'getSortingOptions']);
    Route::get('/',[\App\Http\Controllers\Api\ProductController::class,'getAllSimpleProducts']);
    Route::get('{product:url}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::get('suggestions/get',[\App\Http\Controllers\Api\ProductController::class, 'topSuggestProduct']);

});


Route::prefix('cart')->group(function () {
    Route::post('guest-credential', [\App\Http\Controllers\Api\CartController::class, 'ensureGuestCartCredential']); // only for guest
    Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('add/{product:sku}', [\App\Http\Controllers\Api\CartController::class, 'addProduct']);
    Route::post('update/{product:sku}', [\App\Http\Controllers\Api\CartController::class, 'updateProduct']);
    Route::delete('remove/{product:sku}', [\App\Http\Controllers\Api\CartController::class, 'removeProduct']);
    Route::post('coupon/{voucher_code:code}',[\App\Http\Controllers\Api\CartController::class, 'applyCoupon']);
    Route::post('clear', [\App\Http\Controllers\Api\CartController::class, 'clearCart']);
    Route::post('merge', [\App\Http\Controllers\Api\CartController::class, 'mergeGuestCart'])->middleware('auth:sanctum');
});


Route::prefix('provider')->group(function (){
   Route::get('/payment',[\App\Http\Controllers\Api\ProviderController::class,'getPaymentProviders']);
});
