<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//    //return UserResource::make();
//})->middleware('auth:sanctum');


Route::prefix('user')->middleware('auth:sanctum')
    ->group(function (){
        Route::get('/',[\App\Http\Controllers\Api\Auth\SanctumUserController::class,'getUser']);
    });




//Route::prefix('/user')->middleware('auth:sanctum')->group(base_path('routes/apis/user/user.php'));

Route::prefix('account')->group(base_path('routes/apis/user/account.php'));


Route::prefix('/')->group(base_path('routes/apis/user/auth.php'));


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/create', [WalletController::class, 'create']);
});




// Pages Api
//Route::prefix('pages')->group(function () {
//    Route::get('/', [PageController::class, 'index']);       // GET /api/pages
//    Route::post('read', [PageController::class, 'show']);    // GET /api/pages/{url}
//});

Route::get('pages',[PageController::class,'getPages']);





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

// For guest
Route::post('order/place', [\App\Http\Controllers\Api\OrderController::class, 'placeOrder'])->name('order.placed.guest');

Route::prefix('orders')->group(function (){

    Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'getAllOrders'])->name('orders.all');
    Route::get('{order:uuid}',[\App\Http\Controllers\Api\OrderController::class,'getOrderDetail']);
    Route::post('{order:uuid}canceled',[\App\Http\Controllers\Api\OrderController::class,'cancelOrder']);
    Route::post('{order:uuid}return',[\App\Http\Controllers\Api\OrderController::class,'returnOrder']);
    Route::post('{order:uuid}refund',[\App\Http\Controllers\Api\OrderController::class,'refundOrder']);


});


//
//Route::prefix('order')->middleware('auth:sanctum')->group(function (){
//
//    Route::get('all',[\App\Http\Controllers\Api\OrderController::class,'getAllOrderOfCustomer']);
//
//  //  Route::post('place',[\App\Http\Controllers\Api\OrderController::class,'placeOrder'])->name('order.placed');
//    Route::post('validate',[\App\Http\Controllers\Api\OrderController::class,'confirmOrder'])->name('order.validate');
//
//    Route::get('{order:uuid}/get',[\App\Http\Controllers\Api\OrderController::class,'getOrderDetail']);
//    Route::post('{order:uuid}canceled',[\App\Http\Controllers\Api\OrderController::class,'cancelOrder']);
//    Route::post('{order:uuid}return',[\App\Http\Controllers\Api\OrderController::class,'returnOrder']);
//    Route::post('{order:uuid}refund',[\App\Http\Controllers\Api\OrderController::class,'refundOrder']);
//
//
//});


Route::prefix(config('laravel-transaction.callback.prefix', '_transaction'))
    ->middleware(config('laravel-transaction.callback.middleware', []))
    ->group(function (){
   Route::get('/validate/{transaction:uuid}',[\App\Http\Controllers\Api\TransactionController::class,'confirmTransaction'])
       ->name('transaction.validate');

    Route::get('/failed/{transaction:uuid}',[\App\Http\Controllers\Api\TransactionController::class,'failureTransaction'])
        ->name('transaction.failure');

});



Route::prefix('integration')->group(function (){
   Route::get('/payment',[\App\Http\Controllers\Api\IntegrationController::class,'getPaymentIntegrations']);
});


Route::prefix('recruitment')->group(function (){
   Route::get('/',[\App\Http\Controllers\Api\RecruitmentController::class,'index']);
   Route::get('{naukri:url}',[\App\Http\Controllers\Api\RecruitmentController::class,'show']);
   Route::post('{naukri:url}/apply',[\App\Http\Controllers\Api\RecruitmentController::class,'apply'])->middleware('auth:sanctum');
   Route::get('/user-application/all',[\App\Http\Controllers\Api\RecruitmentController::class,'getUserSubmittedApplications'])->middleware('auth:sanctum');
});


// Lifecycle

Route::prefix('lifecycle')->group(function (){
   Route::get('/timeline',[\App\Http\Controllers\Api\LifecycleController::class,'getTimeline']);
   Route::get('/stages',[\App\Http\Controllers\Api\LifecycleController::class,'getAllStages']);
   Route::get('/stage/{stage:url}',[\App\Http\Controllers\Api\LifecycleController::class,'getStage']);
   Route::get('/level/{level:url}',[\App\Http\Controllers\Api\LifecycleController::class,'getLevel']);
   Route::get('/subscribable',[\App\Http\Controllers\Api\LifecycleController::class,'getUserSubscribableStageAndLevel'])->middleware('auth:sanctum');
});

// Promotion

Route::prefix('sales')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('sales.index');
//
//    Route::get('/guest', [SaleController::class, 'guest'])->name('sales.guest');
//    Route::get('/auth', [SaleController::class, 'authWithoutGroup'])->name('sales.auth');
//    Route::get('/group', [SaleController::class, 'group'])->name('sales.group');
});
