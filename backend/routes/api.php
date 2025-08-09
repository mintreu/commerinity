<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//    //return UserResource::make();
//})->middleware('auth:sanctum');






Route::prefix('/user')->middleware('auth:sanctum')->group(base_path('routes/apis/user/user.php'));
Route::prefix('/')->group(base_path('routes/apis/user/auth.php'));


//ADDRESS API

// Event
Route::prefix('geo')->group(base_path('routes/apis/geo-location.php'));





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


Route::prefix('recruitment')->group(function (){
   Route::get('/',[\App\Http\Controllers\Api\RecruitmentController::class,'index']);
   Route::get('{naukri:url}',[\App\Http\Controllers\Api\RecruitmentController::class,'show']);
   Route::post('{naukri:url}/apply',[\App\Http\Controllers\Api\RecruitmentController::class,'apply']);
});


// Lifecycle

Route::prefix('lifecycle')->group(function (){
   Route::get('/timeline',[\App\Http\Controllers\Api\LifecycleController::class,'getTimeline']);
   Route::get('/stages',[\App\Http\Controllers\Api\LifecycleController::class,'getAllStages']);
   Route::get('/stage/{stage:url}',[\App\Http\Controllers\Api\LifecycleController::class,'getStage']);
   Route::get('/level/{level:url}',[\App\Http\Controllers\Api\LifecycleController::class,'getLevel']);
});

