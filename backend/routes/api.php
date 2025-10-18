<?php

use App\Http\Controllers\Api\{Auth\SanctumUserController,
    BeneficiaryController,
    CartController,
    CategoryController,
    FlashDealController,
    HelpDeskController,
    IntegrationController,
    LifecycleController,
    OrderController,
    PageController,
    PostApiController,
    PushNotificationController,
    RecruitmentController,
    SaleController,
    TransactionController,
    WalletController};
use Illuminate\Support\Facades\Route;

// ========================
// 🔐 AUTH / ACCOUNT ROUTES
// ========================

// Auth (register, login, logout, forgot/reset password)
Route::prefix('/')->group(base_path('routes/apis/user/auth.php'));

// Account (profile, address, etc.)
Route::prefix('account')->group(base_path('routes/apis/user/account.php'));

// Get authenticated user (Sanctum)
Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SanctumUserController::class, 'getUser']);
});

// ========================
// 💰 WALLET ROUTES
// ========================
Route::middleware('auth:sanctum')->group(function () {
    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']);             // GET /wallet
        Route::post('create', [WalletController::class, 'create']);      // POST /wallet/create
        Route::get('qr', [WalletController::class, 'qr']);               // GET /wallet/qr (base64 image)

        // Money ops
        Route::post('add-money', [WalletController::class, 'addMoney']); // POST /wallet/add-money
        Route::post('withdraw', [WalletController::class, 'withdraw']);  // POST /wallet/withdraw
        Route::post('send', [WalletController::class, 'send']);          // POST /wallet/send (P2P via wallet UUID)
        Route::post('change-pin', [WalletController::class, 'changePin']); // POST /wallet/change-pin



        Route::post('point-conversion',[WalletController::class,'pointToBalanceConversion']);

    });

    // Beneficiaries (bank/upi)
    Route::prefix('beneficiaries')->group(function () {
        Route::get('/', [BeneficiaryController::class, 'index']);         // list
        Route::post('/', [BeneficiaryController::class, 'store']);        // create
        Route::get('{account:uuid}', [BeneficiaryController::class, 'show']);   // get one
        Route::put('{account:uuid}', [BeneficiaryController::class, 'update']); // update
        Route::delete('{account:uuid}', [BeneficiaryController::class, 'destroy']); // delete
        Route::post('{account:uuid}/default', [BeneficiaryController::class, 'makeDefault']); // set default
    });
});


// ========================
// 📄 PAGES ROUTES
// ========================
Route::get('pages', [PageController::class, 'getPages']); // GET /pages

// ========================
// 🌍 GEO LOCATION ROUTES
// ========================
Route::prefix('geo')->group(base_path('routes/apis/geo-location.php'));

//Route::group(base_path('routes/apis/products.php'));

// Load extra route files
require base_path('routes/apis/products.php');


// ========================
// 🏷️ CATEGORY ROUTES
// ========================
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']); // GET /categories
    Route::get('/with-products', [CategoryController::class, 'getParentCategoriesWithProducts']); // GET /categories/with-products
    Route::get('{category:url}', [CategoryController::class, 'show']); // GET /categories/{url}
});

// ========================
// 📦 PRODUCT ROUTES
// ========================
//Route::prefix('products')->group(function () {
//    Route::get('/', [ProductController::class, 'getAllSimpleProducts']); // GET /products
//    Route::get('filters/get', [ProductController::class, 'getFilterOptions']); // GET /products/filters/get
//    Route::get('sorts/get', [ProductController::class, 'getSortingOptions']); // GET /products/sorts/get
//    Route::get('suggestions/get', [ProductController::class, 'topSuggestProduct']); // GET /products/suggestions/get
//    Route::get('{product:url}', [ProductController::class, 'show']); // GET /products/{url}
//
//
//});






Route::prefix('product')->group(function (){
    // Comments Engagements
    Route::get('engagements/{product:url}', [\App\Http\Controllers\Api\Product\ProductEngagementController::class, 'index']);
    Route::post('engagement/{product:url}', [\App\Http\Controllers\Api\Product\ProductEngagementController::class, 'store'])->middleware(['auth:sanctum']);
    Route::put('engagement/{product_engagement}', [\App\Http\Controllers\Api\Product\ProductEngagementController::class, 'update'])->middleware(['auth:sanctum']);
    Route::delete('engagement/{product_engagement}', [\App\Http\Controllers\Api\Product\ProductEngagementController::class, 'destroy'])->middleware(['auth:sanctum']);
    Route::post('engagement/{product_engagement}/helpfull', [\App\Http\Controllers\Api\Product\ProductEngagementController::class, 'helpFullEngagement'])->middleware(['auth:sanctum']);

    Route::post('wishlist/{product:url}',[\App\Http\Controllers\Api\Product\ProductWishlistController::class,'addWishlist']);
    Route::delete('wishlist/{product:url}',[\App\Http\Controllers\Api\Product\ProductWishlistController::class,'removeWishlist']);
});



// ========================
// 🔥 FLASH DEALS ROUTES
// ========================
Route::prefix('flash-deals')->group(function () {
    Route::get('/', [FlashDealController::class, 'index']); // GET /flash-deals
    Route::get('/stats', [FlashDealController::class, 'getStats']); // GET /flash-deals/stats
    Route::get('/categories', [FlashDealController::class, 'getCategories']); // GET /flash-deals/categories
});




// ========================
// 🛒 CART ROUTES
// ========================
Route::prefix('cart')->group(function () {
    Route::post('guest-credential', [CartController::class, 'ensureGuestCartCredential']); // POST /cart/guest-credential
    Route::post('validate/guest-credential', [CartController::class, 'validateGuestCartCredential']); // POST /cart/guest-credential
    Route::get('/', [CartController::class, 'index']); // GET /cart
    Route::post('add/{product:sku}', [CartController::class, 'addProduct']); // POST /cart/add/{sku}
    Route::post('update/{product:sku}', [CartController::class, 'updateProduct']); // POST /cart/update/{sku}
    Route::delete('remove/{product:sku}', [CartController::class, 'removeProduct']); // DELETE /cart/remove/{sku}
    Route::post('coupon/{voucher_code}', [CartController::class, 'applyCoupon']); // POST /cart/coupon/{code}
    Route::post('clear', [CartController::class, 'clearCart']); // POST /cart/clear
    Route::post('merge', [CartController::class, 'mergeGuestCart'])->middleware('auth:sanctum'); // POST /cart/merge
});

// ========================
// 🧾 ORDER ROUTES
// ========================

// Guest order placement
Route::post('order/place', [OrderController::class, 'placeOrder'])->name('order.placed');
// Stats
Route::get('order/insight',[OrderController::class,'getInsight']); // get user order trend data insight

Route::prefix('orders')->group(function () {

    Route::get('/', [OrderController::class, 'getAllOrders'])->name('orders.all'); // GET /orders
    Route::get('{order:uuid}', [OrderController::class, 'getOrderDetail']); // GET /orders/{uuid}
    Route::post('{order:uuid}/canceled', [OrderController::class, 'cancelOrder']); // POST /orders/{uuid}/canceled
    Route::post('{order:uuid}/return', [OrderController::class, 'returnOrder']);   // POST /orders/{uuid}/return
    Route::post('{order:uuid}/refund', [OrderController::class, 'refundOrder']);   // POST /orders/{uuid}/refund
    Route::get('{order:uuid}/invoice', [OrderController::class, 'getOrderInvoicePdf']);   // POST /orders/{uuid}/refund

});

// ========================
// 💳 TRANSACTION ROUTES
// ========================
Route::prefix(config('laravel-transaction.callback.prefix', '_transaction'))
    ->middleware(config('laravel-transaction.callback.middleware', []))
    ->group(function () {
        Route::get('/validate/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionActionController::class, 'confirmTransaction'])->name('transaction.validate');
        Route::get('/failed/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionActionController::class, 'failureTransaction'])->name('transaction.failure');
    });

// Transactions
Route::prefix('transactions')
    ->middleware('auth:sanctum')
    ->group(function (){
        Route::get('/', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'index']);
        Route::get('/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'show']);
        Route::get('/{transaction:uuid}/request_pdf', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'sendInvoiceToEmail']);
    });



// ========================
// 🔗 INTEGRATION ROUTES
// ========================
Route::prefix('integration')->group(function () {
    Route::get('/payment', [IntegrationController::class, 'getPaymentIntegrations']); // GET /integration/payment
});

// ========================
// 👩‍💼 RECRUITMENT ROUTES
// ========================
Route::prefix('recruitment')->group(function () {
    Route::get('/', [RecruitmentController::class, 'index']); // GET /recruitment
    Route::get('{naukri:url}', [RecruitmentController::class, 'show']); // GET /recruitment/{url}
    Route::post('{naukri:url}/apply', [RecruitmentController::class, 'apply'])->middleware('auth:sanctum'); // POST /recruitment/{url}/apply
});

// ========================
// 🔄 LIFECYCLE ROUTES
// ========================
Route::prefix('lifecycle')->group(function () {
    Route::get('/timeline', [LifecycleController::class, 'getTimeline']); // GET /lifecycle/timeline
    Route::get('/stages', [LifecycleController::class, 'getAllStages']); // GET /lifecycle/stages
    Route::get('/stage/{stage:url}', [LifecycleController::class, 'getStage']); // GET /lifecycle/stage/{url}
    Route::get('/level/{level:url}', [LifecycleController::class, 'getLevel']); // GET /lifecycle/level/{url}
    Route::get('/subscribable', [LifecycleController::class, 'getUserSubscribableStageAndLevel'])->middleware('auth:sanctum'); // GET /lifecycle/subscribable
});

// ========================
// 🎉 SALES / PROMOTIONS ROUTES
// ========================
Route::prefix('sales')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('sales.index'); // GET /sales
});


// ========================
// 🎉 HelpDesk / Support ROUTES
// ========================

Route::prefix('helpdesk')->group(function () {
    Route::get('topics/ticket', [HelpDeskController::class, 'getTicketTopics']);
    Route::get('topics/faq', [HelpDeskController::class, 'getFaqTopics']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('tickets', [HelpDeskController::class, 'getAllTickets']);
        Route::post('tickets', [HelpDeskController::class, 'storeTicket']);
        Route::get('tickets/{helpdesk:uuid}', [HelpDeskController::class, 'viewTicket']);
        Route::post('tickets/{helpdesk:uuid}/reply', [HelpDeskController::class, 'reply']);
        Route::post('tickets/{helpdesk:uuid}/attachments', [HelpDeskController::class, 'uploadAttachment']);
    });
});


// ========================
// 🎉 Contact Us / Business Enquire Store Route
// ========================


Route::post('/contact/user', [\App\Http\Controllers\Api\InquiryController::class, 'storeUser']);
Route::post('/contact/business', [\App\Http\Controllers\Api\InquiryController::class, 'storeBusiness']);



Route::prefix('blogs')->group(function () {
    Route::get('/', [PostApiController::class, 'index'])->name('api.posts.index');
    Route::get('/{post:url}', [PostApiController::class, 'show'])->name('api.posts.show');
});



// Public routes
Route::get('/push/vapid-public-key', [PushNotificationController::class, 'getVapidPublicKey']);
Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/push/unsubscribe', [PushNotificationController::class, 'unsubscribe']);

    // Admin routes (add your admin middleware)
    Route::middleware('admin')->group(function () {
        Route::post('/push/send-to-user', [PushNotificationController::class, 'sendToUser']);
        Route::post('/push/send-to-all', [PushNotificationController::class, 'sendToAll']);
        Route::post('/push/send-to-level', [PushNotificationController::class, 'sendToLevel']);
    });
});
