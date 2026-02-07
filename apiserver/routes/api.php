<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AdvisorEarningsController;
use App\Http\Controllers\Api\Affiliate\AffiliateFundController;
use App\Http\Controllers\Api\Affiliate\AffiliateLedgerController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\BeneficiaryAccountController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\Dashboard\AdvisorTeamLeaderController;
use App\Http\Controllers\Api\Dashboard\AppointmentController;
use App\Http\Controllers\Api\Dashboard\ChallengeController;
use App\Http\Controllers\Api\Dashboard\ProgramController;
use App\Http\Controllers\Api\DealsController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Notification\PushSubscriptionController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\Order\OrderDisplayController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicProfileController;
use App\Http\Controllers\Api\RecruitmentController;
use App\Http\Controllers\Api\Rewards\RewardEarningController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TrendController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\Webhooks\CashfreeWebhookController;
use App\Http\Controllers\Api\Webhooks\RazorpayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// Health Check
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Debug route
Route::get('/debug-auth-flow', [\App\Http\Controllers\DebugAuthController::class, 'testFlow']);

// ========================================
// Auth Routes - Public
// ========================================

// OTP
Route::post('/auth/send-otp', [OtpController::class, 'send']);
Route::post('/auth/verify-otp', [OtpController::class, 'verify']);

// Registration
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/register-email', [RegisterController::class, 'registerWithEmail']);

// Login
Route::post('/auth/login', [LoginController::class, 'login']); // Nuxt frontend
Route::post('/auth/login-mobile', [LoginController::class, 'loginMobile']); // Android/iOS apps

// Password Reset (rate limited: 5 attempts per hour)
Route::middleware('throttle:password-reset')->group(function () {
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/auth/forgot-password-mobile', [PasswordResetController::class, 'forgotPasswordMobile']);
});
Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/reset-password-mobile', [PasswordResetController::class, 'resetPasswordMobile']);

// Logout (optional auth - works with or without token)
Route::post('/auth/logout', [LoginController::class, 'logout']);
Route::post('/auth/logout-all', [LoginController::class, 'logoutAll']);

// ========================================
// Protected Routes
// ========================================

Route::middleware('auth:sanctum')->group(function () {
    // User Profile
    Route::get('/user', function (Request $request) {
        return (new \App\Http\Resources\UserResource($request->user()))->response();
    });

    // Profile Management
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::put('/user/password', [ProfileController::class, 'changePassword']);

    // Onboarding
    Route::get('/onboarding/status', [OnboardingController::class, 'status']);
    Route::put('/onboarding/profile', [OnboardingController::class, 'updateProfile']);
    Route::post('/onboarding/verify-contact', [OnboardingController::class, 'verifyContact']);
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete']);

    // Address Management
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{address:uuid}', [AddressController::class, 'show']);
    Route::put('/addresses/{address:uuid}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address:uuid}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{address:uuid}/default', [AddressController::class, 'setDefault']);

    // KYC Management
    Route::get('/kyc/status', [KycController::class, 'status']);
    Route::post('/kyc/submit', [KycController::class, 'submit']);
    Route::post('/kyc/{kyc}/resubmit', [KycController::class, 'resubmit']);

    // ========================================
    // Notifications
    // ========================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Push Subscriptions (WebPush/VAPID)
    Route::prefix('push')->group(function () {
        Route::post('/subscribe', [PushSubscriptionController::class, 'store']);
        Route::post('/unsubscribe', [PushSubscriptionController::class, 'destroy']);
    });

    // ========================================
    // Account Management
    // ========================================
    Route::delete('/account', [AccountController::class, 'destroy']);

    // Affiliate Tree
    Route::prefix('affiliate')->group(function () {
        Route::get('/stats', [AccountController::class, 'affiliateStats']);
        Route::get('/children', [AccountController::class, 'directChildren']);
        Route::get('/upline', [AccountController::class, 'upline']);
        Route::get('/tree', [AccountController::class, 'tree']);
        Route::get('/disbursements', [\App\Http\Controllers\Api\Affiliate\AffiliateDisbursementController::class, 'index']);
        Route::get('/disbursements/{payout:uuid}', [\App\Http\Controllers\Api\Affiliate\AffiliateDisbursementController::class, 'show']);
        Route::get('/disbursements/{payout:uuid}/invoice', [\App\Http\Controllers\Api\Affiliate\AffiliateDisbursementController::class, 'invoice']);
        Route::get('/ledger', [AffiliateLedgerController::class, 'index']);
        Route::get('/funds', [AffiliateFundController::class, 'accounts']);
        Route::get('/funds/{fundType}/transactions', [AffiliateFundController::class, 'transactions']);
    });

    // ========================================
    // Commissions / Earnings
    // ========================================
    Route::get('/advisor/earnings', [AdvisorEarningsController::class, 'show']);
    Route::prefix('commissions')->group(function () {
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/summary', [CommissionController::class, 'summary']);
        Route::get('/by-type', [CommissionController::class, 'byType']);
        Route::get('/monthly', [CommissionController::class, 'monthly']);
        Route::get('/{uuid}', [CommissionController::class, 'show']);
    });

    // ========================================
    // Wallet Management
    // ========================================
    Route::prefix('wallet')->group(function () {
        // Basic wallet info
        Route::get('/', [WalletController::class, 'show']);
        Route::get('/balance', [WalletController::class, 'balance']);
        Route::get('/stats', [WalletController::class, 'stats']);
        Route::get('/transactions', [WalletController::class, 'transactions']);

        // Security questions
        Route::get('/security-questions', [WalletController::class, 'getSecurityQuestions']);
        Route::get('/my-security-questions', [WalletController::class, 'getUserSecurityQuestions']);

        // PIN Management
        Route::post('/setup-pin', [WalletController::class, 'setupPin']);
        Route::post('/request-pin-otp', [WalletController::class, 'requestPinChangeOtp']);
        Route::post('/change-pin', [WalletController::class, 'changePin']);
        Route::post('/verify-pin', [WalletController::class, 'verifyPin']);
        Route::post('/verify-security-question', [WalletController::class, 'verifySecurityQuestion']);
        Route::post('/reset-pin', [WalletController::class, 'resetPinWithToken']);

        // Payment Operations
        Route::post('/topup', [WalletController::class, 'topup']); // ⭐ NEW - Add money

        // Financial Transactions (PIN required)
        Route::post('/send', [WalletController::class, 'sendMoney']);
        Route::post('/withdraw', [WalletController::class, 'withdraw']);
        Route::post('/pay', [WalletController::class, 'payViaWallet']);

        // Beneficiary Accounts (Bank Accounts for withdrawals)
        Route::prefix('beneficiaries')->group(function () {
            Route::get('/', [BeneficiaryAccountController::class, 'index']);
            Route::post('/', [BeneficiaryAccountController::class, 'store']);
            Route::get('/types', [BeneficiaryAccountController::class, 'getAccountTypes']);
            Route::post('/verify-ifsc', [BeneficiaryAccountController::class, 'verifyIfsc']);
            Route::get('/{uuid}', [BeneficiaryAccountController::class, 'show']);
            Route::put('/{uuid}', [BeneficiaryAccountController::class, 'update']);
            Route::delete('/{uuid}', [BeneficiaryAccountController::class, 'destroy']);
            Route::post('/{uuid}/restore', [BeneficiaryAccountController::class, 'restore']);
            Route::post('/{uuid}/default', [BeneficiaryAccountController::class, 'setDefault']);
            Route::post('/{uuid}/verify', [BeneficiaryAccountController::class, 'verify']);
        });
    });

    // ========================================
    // Payouts (Cashfree Payout System)
    // ========================================
    Route::prefix('payouts')->group(function () {
        // Admin: Credit user wallet (commissions, affiliate, refunds)
        Route::post('/to-wallet', [PayoutController::class, 'payoutToWallet']);

        // Cashgram (Admin/Distributor - payout links)
        Route::post('/cashgram', [PayoutController::class, 'createCashgram']);
        Route::get('/cashgram/{cashgramId}/status', [PayoutController::class, 'cashgramStatus']);

        // Admin Utility
        Route::get('/balance', [PayoutController::class, 'getBalance']);
    });

    // ========================================
    // Job Applications (Protected)
    // ========================================
    Route::post('/careers/{slug}/apply', [RecruitmentController::class, 'apply']);
    Route::get('/careers/{slug}/check-application', [RecruitmentController::class, 'checkApplication']);

    Route::prefix('my-applications')->group(function () {
        Route::get('/', [RecruitmentController::class, 'myApplications']);
        Route::get('/{uuid}', [RecruitmentController::class, 'showApplication']);
        Route::post('/{uuid}/withdraw', [RecruitmentController::class, 'withdrawApplication']);
        Route::post('/{uuid}/pay', [RecruitmentController::class, 'initiatePayment']);
    });

    // ========================================
    // Subscription Management
    // ========================================
    Route::prefix('subscription')->group(function () {
        Route::get('/plans', [SubscriptionController::class, 'plans']);
        Route::get('/status', [SubscriptionController::class, 'status']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::get('/history', [SubscriptionController::class, 'history']);
    });

    // ========================================
    // Dashboard Modules (Appointments / Challenges / Programs)
    // ========================================
    Route::prefix('dashboard')->group(function () {
        Route::apiResource('appointments', AppointmentController::class)->only(['index', 'store', 'show']);
        Route::get('appointments/attendee-types', [AppointmentController::class, 'attendeeTypes']);
        Route::get('appointments/search-users', [AppointmentController::class, 'searchUsers']);

        Route::prefix('challenges')->group(function () {
            Route::get('/', [ChallengeController::class, 'index']);
            Route::get('/active', [ChallengeController::class, 'active']);
            Route::get('/{challenge:uuid}', [ChallengeController::class, 'show']);
        });

        Route::prefix('programs')->group(function () {
            Route::get('/', [ProgramController::class, 'index']);
            Route::post('/', [ProgramController::class, 'store']);
            Route::get('/{program:uuid}', [ProgramController::class, 'show']);
        });

        Route::post('/advisor/team-leaders', [AdvisorTeamLeaderController::class, 'store']);
    });

    // ========================================
    // Dashboard Notices (Promotional messages from admin)
    // ========================================
    Route::prefix('notices')->group(function () {
        Route::get('/', [NoticeController::class, 'index']);
        Route::get('/{notice}', [NoticeController::class, 'show']);
        Route::post('/{notice}/dismiss', [NoticeController::class, 'dismiss']);
        Route::post('/{notice}/click', [NoticeController::class, 'click']);
    });

    // ========================================
    // Public Profile (View other users in Affiliate tree)
    // Visibility rules: upline can see downline, limited data
    // ========================================
    Route::prefix('users')->group(function () {
        Route::get('/{user:uuid}/profile', [PublicProfileController::class, 'show']);
        Route::get('/{user:uuid}/team', [PublicProfileController::class, 'team']);
    });

    // ========================================
    // Messaging (For subscribed members only - Member, Promoter, Advisor, Mentor)
    // Team communication - not for regular users
    // ========================================
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index']);
        Route::get('/broadcasts', [MessageController::class, 'broadcasts']);
        Route::get('/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/recipients', [MessageController::class, 'availableRecipients']);
        Route::post('/', [MessageController::class, 'create']);
        Route::get('/{conversation}', [MessageController::class, 'show']);
        Route::post('/{conversation}', [MessageController::class, 'store']);
        Route::post('/{conversation}/read', [MessageController::class, 'markAsRead']);
        Route::delete('/message/{message}', [MessageController::class, 'destroy']);
    });

    // ========================================
    // Trends & Charts API
    // ========================================
    Route::prefix('trends')->group(function () {
        // Dashboard summary
        Route::get('/dashboard', [TrendController::class, 'dashboardSummary']);

        // Wallet trends
        Route::get('/wallet/balance', [TrendController::class, 'walletBalance']);
        Route::get('/wallet/credit-debit', [TrendController::class, 'walletCreditDebit']);
        Route::get('/wallet/activity', [TrendController::class, 'walletActivity']);
        Route::get('/wallet/comparison', [TrendController::class, 'walletComparison']);

        // Transaction trends
        Route::get('/transactions/volume', [TrendController::class, 'transactionVolume']);

        // Commission trends
        Route::get('/commissions/earnings', [TrendController::class, 'commissionEarnings']);
        Route::get('/commissions/by-type', [TrendController::class, 'commissionByType']);
        Route::get('/commissions/comparison', [TrendController::class, 'commissionComparison']);

        // Team trends
        Route::get('/team/growth', [TrendController::class, 'teamGrowth']);
        Route::get('/team/levels', [TrendController::class, 'teamLevels']);
        Route::get('/team/activity', [TrendController::class, 'teamActivity']);
    });

    // ========================================
    // Activity Tracking (Client-side logging)
    // Activities are stored for admin analytics only
    // ========================================
    Route::prefix('activity')->group(function () {
        Route::post('/track', [ActivityController::class, 'track']);
        Route::post('/page-view', [ActivityController::class, 'trackPageView']);
        Route::post('/action', [ActivityController::class, 'trackAction']);
        Route::post('/batch', [ActivityController::class, 'trackBatch']);
    });

    // ========================================
    // Rewards (Coins / Voucher)
    // ========================================
    Route::prefix('rewards')->group(function () {
        Route::get('/', [RewardEarningController::class, 'index']);
        Route::post('/{reward:uuid}/use', [RewardEarningController::class, 'markUsed']);
    });
});

// ========================================
// Public Routes (No Auth Required)
// ========================================

// Public Homepage Stats (cached, no auth)
Route::get('/public/stats', [\App\Http\Controllers\Api\PublicStatsController::class, 'homepage']);

// Geo Data (Public reference data for address forms)
Route::get('/geo/countries', [\App\Http\Controllers\Api\GeoController::class, 'countries']);
Route::get('/geo/states', [\App\Http\Controllers\Api\GeoController::class, 'states']);
Route::get('/geo/blocks', [\App\Http\Controllers\Api\GeoController::class, 'blocks']);
Route::get('/geo/districts', [\App\Http\Controllers\Api\GeoController::class, 'districts']);

// VAPID Public Key (needed for push notification registration)
Route::get('/push/vapid-key', [PushSubscriptionController::class, 'vapidPublicKey']);

// ========================================
// Careers / Recruitment (Public)
// ========================================
Route::prefix('careers')->group(function () {
    Route::get('/', [RecruitmentController::class, 'index']);
    Route::get('/filters', [RecruitmentController::class, 'filters']);
    Route::get('/{slug}', [RecruitmentController::class, 'show']);
});

// ========================================
// Contact / Inquiry (Public)
// ========================================
Route::prefix('contact')->group(function () {
    Route::post('/user', [\App\Http\Controllers\Api\InquiryController::class, 'storeUser']);
    Route::post('/business', [\App\Http\Controllers\Api\InquiryController::class, 'storeBusiness']);
});

// ========================================
// Checkout (Public - no auth for checkout page display)
// no auth thats a bad thing, not secured
// ========================================
Route::prefix('checkout')->group(function () {
    Route::get('/{transaction:uuid}', [CheckoutController::class, 'show']);
    Route::get('/{transaction:uuid}/status', [CheckoutController::class, 'status']);
});

// ========================
// 💳 TRANSACTION ROUTES
// ========================
Route::prefix('_transaction')
    ->group(function () {
        Route::get('/validate/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionActionController::class, 'confirmTransaction'])->name('transaction.validate');
        Route::get('/failed/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionActionController::class, 'failureTransaction'])->name('transaction.failure');
    });

// ========================================
// Webhooks (No Auth - Signature Verified)
// ========================================
Route::prefix('webhooks')->group(function () {
    // Cashfree Payment Gateway
    Route::post('/cashfree', [CashfreeWebhookController::class, 'handle']);
    Route::post('/cashfree/payout', [CashfreeWebhookController::class, 'handlePayout']);

    // Razorpay Payment Gateway
    Route::post('/razorpay', [RazorpayWebhookController::class, 'handle']);
});

// ========================================
// FAQ - Public (No auth required)
// ========================================
Route::prefix('faq')->group(function () {
    Route::get('/topics', [\App\Http\Controllers\Api\FaqController::class, 'topics']);
    Route::get('/popular', [\App\Http\Controllers\Api\FaqController::class, 'popular']);
    Route::get('/search', [\App\Http\Controllers\Api\FaqController::class, 'search']);
    Route::get('/view/{url}', [\App\Http\Controllers\Api\FaqController::class, 'show']);
    Route::get('/{topicSlug}', [\App\Http\Controllers\Api\FaqController::class, 'byTopic']);
    Route::post('/{url}/helpful', [\App\Http\Controllers\Api\FaqController::class, 'markHelpful']);
    Route::post('/{url}/not-helpful', [\App\Http\Controllers\Api\FaqController::class, 'markNotHelpful']);
});

// ========================================
// Helpdesk / Support Tickets
// ========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/helpdesk/tickets', [\App\Http\Controllers\Api\TicketController::class, 'index']);
    Route::post('/helpdesk/tickets', [\App\Http\Controllers\Api\TicketController::class, 'store']);
    Route::get('/helpdesk/tickets/{ticket:uuid}', [\App\Http\Controllers\Api\TicketController::class, 'show']);
    Route::post('/helpdesk/tickets/{ticket:uuid}/reply', [\App\Http\Controllers\Api\TicketController::class, 'reply']);
    Route::get('/helpdesk/topics/ticket', [\App\Http\Controllers\Api\TicketController::class, 'topics']);
});

// ========================================
// Catalog / Products (Public)
// ========================================
Route::prefix('catalog')->group(function () {
    Route::get('/products', [\App\Http\Controllers\Api\CatalogController::class, 'products']);
    Route::get('/products/{product:url}', [\App\Http\Controllers\Api\CatalogController::class, 'show']);
    Route::get('/categories', [\App\Http\Controllers\Api\CatalogController::class, 'categories']);
    Route::get('/categories/featured', [\App\Http\Controllers\Api\CatalogController::class, 'featuredCategories']);
    Route::get('/category/{category:url}', [\App\Http\Controllers\Api\CatalogController::class, 'category']);
    Route::get('/featured', [\App\Http\Controllers\Api\CatalogController::class, 'featured']);
    Route::get('/on-sale', [\App\Http\Controllers\Api\CatalogController::class, 'onSale']);
    Route::get('/search', [\App\Http\Controllers\Api\CatalogController::class, 'search']);
    Route::get('/filters', [\App\Http\Controllers\Api\CatalogController::class, 'filters']);

    Route::get('/on-deal',[DealsController::class,'index']);
});

// ========================================
// Advertisements (Public)
// ========================================
Route::prefix('ads')->group(function () {
    Route::get('/placements', [\App\Http\Controllers\Api\AdvertisementController::class, 'placements']);
    Route::get('/page', [\App\Http\Controllers\Api\AdvertisementController::class, 'forPage']);
    Route::get('/{placement}', [\App\Http\Controllers\Api\AdvertisementController::class, 'forPlacement']);
    Route::get('/{placement}/{block}', [\App\Http\Controllers\Api\AdvertisementController::class, 'forBlock']);
    Route::post('/{advertisement}/click', [\App\Http\Controllers\Api\AdvertisementController::class, 'recordClick']);
});

// ========================================
// Cart / Shopping (Authenticated Users Only)
// ========================================
Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::get('/count', [CartController::class, 'count']);
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::post('/coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/coupon', [CartController::class, 'removeCoupon']);
    Route::put('/{productId}', [CartController::class, 'update']);
    Route::delete('/{productId}', [CartController::class, 'destroy']);
    Route::delete('/', [CartController::class, 'clear']);
});

// ========================================
// Product Reviews (Public read, Auth write)
// ========================================
Route::prefix('products/{product:url}/reviews')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ProductEngagementController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\ProductEngagementController::class, 'store']);
});
Route::prefix('reviews')->middleware('auth:sanctum')->group(function () {
    Route::put('/{engagement}', [\App\Http\Controllers\Api\ProductEngagementController::class, 'update']);
    Route::delete('/{engagement}', [\App\Http\Controllers\Api\ProductEngagementController::class, 'destroy']);
    Route::post('/{engagement}/helpful', [\App\Http\Controllers\Api\ProductEngagementController::class, 'markHelpful']);
});

// ========================================
// Wishlist (Auth required)
// ========================================
Route::prefix('wishlist')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/{product:url}', [\App\Http\Controllers\Api\WishlistController::class, 'store']);
    Route::delete('/{product:url}', [\App\Http\Controllers\Api\WishlistController::class, 'destroy']);
    Route::post('/{product:url}/toggle', [\App\Http\Controllers\Api\WishlistController::class, 'toggle']);
});
Route::get('/wishlist/{product:url}/check', [\App\Http\Controllers\Api\WishlistController::class, 'check']);

// ========================================
// Orders (Auth required) [ECOMMERCE]
// ========================================
Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [OrderDisplayController::class, 'index']);
    Route::get('/stats', [OrderDisplayController::class, 'stats']);
    Route::get('/{uuid}', [OrderDisplayController::class, 'show']);
});

Route::get('orders/{uuid}/invoice', [OrderDisplayController::class, 'invoice']);

Route::prefix('order')->middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [\App\Http\Controllers\Api\Order\OrderActionController::class, 'checkout']);
    Route::post('/return', [\App\Http\Controllers\Api\Order\OrderActionController::class, 'requestReturn']);
    Route::post('/refund', [\App\Http\Controllers\Api\Order\OrderActionController::class, 'requestRefund']);
});
