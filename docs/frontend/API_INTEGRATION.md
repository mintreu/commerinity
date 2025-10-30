# Frontend API Integration Documentation

This document provides a detailed overview of how the frontend application interacts with the backend API. It aims to map frontend API calls to their corresponding backend routes and controllers, identify unused APIs, and suggest areas for further API integration.

## Table of Contents
- [Overview](#overview)
- [API Call Mapping](#api-call-mapping)
  - [Cart Management (`frontend/composables/useCart.ts`)](#cart-management-frontendcomposablesusecartts)
  - [Wishlist Management (`frontend/composables/useWishlist.ts`)](#wishlist-management-frontendcomposablesusewishlistts)
  - [Authentication](#authentication)
  - [Product Related APIs](#product-related-apis)
  - [User Account & Profile](#user-account--profile)
  - [Other Pages/Components](#other-pagescomponents)
- [Unused Backend API Routes](#unused-backend-api-routes)
- [Static Data to API Conversion](#static-data-to-api-conversion)
- [Full Integration Workflow](#full-integration-workflow)

## Overview

The frontend application, built with Nuxt.js 3, communicates with the Laravel 12 backend API primarily using `useSanctumFetch` (provided by `@qirolab/nuxt-sanctum-authentication`) for authenticated and guest API requests. The base API URL is configured via `config.public.apiBase`.

## API Call Mapping

This section details the mapping of frontend API calls to their respective backend routes and the controllers/methods that handle them.

### Cart Management (`frontend/composables/useCart.ts`)

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `POST /cart/validate/guest-credential` | `Route::post('validate/guest-credential', [CartController::class, 'validateGuestCartCredential']);` (Line 100) | `App\Http\Controllers\Api\CartController@validateGuestCartCredential` |
| `POST /cart/guest-credential` | `Route::post('guest-credential', [CartController::class, 'ensureGuestCartCredential']);` (Line 99) | `App\Http\Controllers\Api\CartController@ensureGuestCartCredential` |
| `GET /cart` | `Route::get('/', [CartController::class, 'index']);` (Line 101) | `App\Http\Controllers\Api\CartController@index` |
| `POST /cart/add/{sku}` | `Route::post('add/{product:sku}', [CartController::class, 'addProduct']);` (Line 102) | `App\Http\Controllers\Api\CartController@addProduct` |
| `POST /cart/update/{sku}` | `Route::post('update/{product:sku}', [CartController::class, 'updateProduct']);` (Line 103) | `App\Http\Controllers\Api\CartController@updateProduct` |
| `DELETE /cart/remove/{sku}` | `Route::delete('remove/{product:sku}', [CartController::class, 'removeProduct']);` (Line 104) | `App\Http\Controllers\Api\CartController@removeProduct` |
| `POST /cart/coupon/{code}` | `Route::post('coupon/{voucher_code}', [CartController::class, 'applyCoupon']);` (Line 105) | `App\Http\Controllers\Api\CartController@applyCoupon` |
| `POST /cart/clear` | `Route::post('clear', [CartController::class, 'clearCart']);` (Line 106) | `App\Http\Controllers\Api\CartController@clearCart` |
| `POST /cart/merge` | `Route::post('merge', [CartController::class, 'mergeGuestCart'])->middleware('auth:sanctum');` (Line 107) | `App\Http\Controllers\Api\CartController@mergeGuestCart` |

### Wishlist Management (`frontend/composables/useWishlist.ts`)

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `POST /product/wishlist/{productUrl}` | `Route::post('wishlist/{product:url}',[\App\Http\Controllers\Api\Product\ProductWishlistController::class,'addWishlist']);` (Line 86) | `App\Http\Controllers\Api\Product\ProductWishlistController@addWishlist` |
| `DELETE /product/wishlist/{productUrl}` | `Route::delete('wishlist/{product:url}',[\App\Http\Controllers\Api\Product\ProductWishlistController::class,'removeWishlist']);` (Line 87) | `App\Http\Controllers\Api\Product\ProductWishlistController@removeWishlist` |

### Authentication

Authentication-related API calls are primarily handled by the `useSanctum` composable and specific endpoints for OTP-based flows and social logins.

#### `frontend/pages/auth/forgot-password.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `POST /auth/verify-otp` | `Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);` (Line 14) | `App\Http\Controllers\Api\Auth\AuthController@verifyOtp` |
| `POST /auth/reset-password` | `Route::post('reset_password',[AuthController::class,'resetPassword']);` (Line 16) | `App\Http\Controllers\Api\Auth\AuthController@resetPassword` |

#### `frontend/pages/auth/login.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `POST /auth/verify-otp` | `Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);` (Line 14) | `App\Http\Controllers\Api\Auth\AuthController@verifyOtp` |
| `login()` (via `useSanctum`) | `Route::post('login',[AuthController::class,'login']);` (Line 10) | `App\Http\Controllers\Api\Auth\AuthController@login` |
| Redirect to `/auth/google/redirect` | `Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Web\SocialLoginController::class,'attempt']);` (Line 10 in `backend/routes/web.php`) | `App\Http\Controllers\Web\SocialLoginController@attempt` |

#### `frontend/components/RegisterForm.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/has_contact` | `Route::post('/auth/has_contact',[AuthController::class,'checkContactExistence']);` (Line 12) | `App\Http\Controllers\Api\Auth\AuthController@checkContactExistence` |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `POST /auth/verify-otp` | `Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);` (Line 14) | `App\Http\Controllers\Api\Auth\AuthController@verifyOtp` |
| `POST /register` | `Route::post('register',[AuthController::class,'register']);` (Line 15) | `App\Http\Controllers\Api\Auth\AuthController@register` |
| `login()` (via `useSanctum`) | `Route::post('login',[AuthController::class,'login']);` (Line 10) | `App\Http\Controllers\Api\Auth\AuthController@login` |

#### `frontend/pages/dashboard/account/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/stats` | `Route::get('/',[\App\Http\Controllers\Api\Auth\UserStatsController::class,'index']);` (Line 100, within `stats` prefix group) | `App\Http\Controllers\Api\Auth\UserStatsController@index` |
| `GET /account/activity` | (Assumed: No explicit route found, likely part of a broader stats endpoint or a dedicated activity controller) | (To be determined, e.g., `App\Http\Controllers\Api\Auth\UserActivityController@index`) |
| `PUT /account/profile` | `Route::put('/profile', [UserAccountController::class, 'updateProfile'])->name('account.profile.update');` (Line 20) | `App\Http\Controllers\Api\Auth\UserAccountController@updateProfile` |

#### `frontend/pages/dashboard/account/edit.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/profile` | `Route::get('/profile', [SanctumUserController::class, 'getProfile'])->name('account.profile.show');` (Line 19) | `App\Http\Controllers\Api\Auth\SanctumUserController@getProfile` |
| `PUT /account/profile` | `Route::put('/profile', [UserAccountController::class, 'updateProfile'])->name('account.profile.update');` (Line 20) | `App\Http\Controllers\Api\Auth\UserAccountController@updateProfile` |

#### `frontend/components/account/AvatarUploader.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `PUT /account/avatar` | `Route::put('/avatar', [UserAccountController::class, 'updateAvatar'])->name('account.avatar.update');` (Line 34) | `App\Http\Controllers\Api\Auth\UserAccountController@updateAvatar` |

#### `frontend/components/account/ChangeEmail.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/has_contact` | `Route::post('/auth/has_contact',[AuthController::class,'checkContactExistence']);` (Line 12 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@checkContactExistence` |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `POST /auth/verify-otp` | `Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);` (Line 14 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@verifyOtp` |
| `PUT /account/contact` | `Route::put('/contact', [UserAccountController::class, 'updateContact'])->name('account.contact.update');` (Line 29 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAccountController@updateContact` |

#### `frontend/components/account/ChangeMobile.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/has_contact` | `Route::post('/auth/has_contact',[AuthController::class,'checkContactExistence']);` (Line 12 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@checkContactExistence` |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `POST /auth/verify-otp` | `Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);` (Line 14 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@verifyOtp` |
| `PUT /account/contact` | `Route::put('/contact', [UserAccountController::class, 'updateContact'])->name('account.contact.update');` (Line 29 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAccountController@updateContact` |

#### `frontend/components/account/ChangePassword.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `PUT /account/password` | `Route::put('/password', [UserAccountController::class, 'updatePassword'])->name('account.password.update');` (Line 40) | `App\Http\Controllers\Api\Auth\UserAccountController@updatePassword` |

#### `frontend/components/account/DeleteAccount.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/auth.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `POST /auth/send-otp` | `Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);` (Line 13 in `backend/routes/apis/user/auth.php`) | `App\Http\Controllers\Api\Auth\AuthController@sendOtp` |
| `DELETE /account/delete` | `Route::delete('/delete', [UserAccountController::class, 'deleteAccount']);` (Line 46 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAccountController@deleteAccount` |

#### `frontend/components/account/ExportData.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `POST /account/export-data` | `Route::post('/export-data', [UserAccountController::class, 'exportData']);` (Line 45) | `App\Http\Controllers\Api\Auth\UserAccountController@exportData` |

#### `frontend/pages/dashboard/account/onboarding.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `GET /user/my-profile` | `Route::get('/', [SanctumUserController::class, 'getUser'])->name('account.show');` (Line 16 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\SanctumUserController@getUser` |
| `GET /geo/countries` | `Route::prefix('geo')->group(base_path('routes/apis/geo-location.php'));` (in `backend/routes/api.php`, which then calls `Route::get('/countries', [GeoController::class, 'getCountries']);` in `backend/routes/apis/geo-location.php`) | `App\Http\Controllers\Api\GeoController@getCountries` |
| `GET /geo/states/IN` | `Route::get('/states/{country_code}', [GeoController::class, 'getStates']);` (in `backend/routes/apis/geo-location.php`) | `App\Http\Controllers\Api\GeoController@getStates` |
| `GET /geo/state/{state_code}` | `Route::get('/state/{state_code}', [GeoController::class, 'getStateBlocksAndDistricts']);` (in `backend/routes/apis/geo-location.php`) | `App\Http\Controllers\Api\GeoController@getStateBlocksAndDistricts` |
| `GET /lifecycle/subscribable` | `Route::get('/subscribable', [LifecycleController::class, 'getUserSubscribableStageAndLevel'])->middleware('auth:sanctum');` (Line 190 in `backend/routes/api.php`) | `App\Http\Controllers\Api\LifecycleController@getUserSubscribableStageAndLevel` |
| `POST /user/onboarding` | `Route::post('/onboarding', [UserOnboardingController::class, 'processOnboarding'])->name('account.onboarding.store');` (Line 52 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserOnboardingController@processOnboarding` |
| `GET https://api.postalpincode.in/pincode/{code}` | (External API) | (External) |

#### `frontend/pages/dashboard/account/address/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `GET /geo/states/IN` | `Route::get('/states/{country_code}', [GeoController::class, 'getStates']);` (in `backend/routes/apis/geo-location.php`) | `App\Http\Controllers\Api\GeoController@getStates` |
| `GET /geo/state/{state_code}` | `Route::get('/state/{state_code}', [GeoController::class, 'getStateBlocksAndDistricts']);` (in `backend/routes/apis/geo-location.php`) | `App\Http\Controllers\Api\GeoController@getStateBlocksAndDistricts` |
| `GET /account/addresses` | `Route::get('/addresses', [UserAddressController::class, 'getUserAllAddress'])->name('account.addresses.index');` (Line 58 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAddressController@getUserAllAddress` |
| `POST /account/addresses` | `Route::post('/addresses', [UserAddressController::class, 'addUserAddress'])->name('account.addresses.store');` (Line 59 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAddressController@addUserAddress` |
| `PUT /account/addresses/{address:uuid}` | `Route::put('/addresses/{address:uuid}', [UserAddressController::class, 'updateUserAddress'])->name('account.addresses.update');` (Line 61 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAddressController@updateUserAddress` |
| `DELETE /account/addresses/{address:uuid}` | (Assumed: No explicit route found, but typically handled by a `destroy` method) | `App\Http\Controllers\Api\Auth\UserAddressController@destroy` (or similar) |
| `GET https://api.postalpincode.in/pincode/{code}` | (External API) | (External) |

#### `frontend/pages/dashboard/account/kyc/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/kyc` | `Route::get('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'getUserKyc']);` (Line 70) | `App\Http\Controllers\Api\Auth\UserKycController@getUserKyc` |
| `POST /account/kyc` | `Route::post('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'addUserKyc']);` (Line 71) | `App\Http\Controllers\Api\Auth\UserKycController@addUserKyc` |
| `PUT /account/kyc` | `Route::put('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'updateUserKyc']);` (Line 72) | `App\Http\Controllers\Api\Auth\UserKycController@updateUserKyc` |

#### `frontend/pages/dashboard/myteam.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/tree` | `Route::get('/tree',[\App\Http\Controllers\Api\Auth\UserStatsController::class, 'getUserTree']);` (Line 109) | `App\Http\Controllers\Api\Auth\UserStatsController@getUserTree` |

#### `frontend/pages/dashboard/subscribe.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/lifecycle/get_status` | `Route::get('/get_status',[\App\Http\Controllers\Api\Auth\UserLifecycleController::class,'getUserSubscriptionStatus']);` (Line 135 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserLifecycleController@getUserSubscriptionStatus` |
| `POST /account/subscription/auto-renew` | (Assumed: No explicit route found in `account.php` or `api.php`, but implied by `updateAutoRenew` function) | (To be determined, e.g., `App\Http\Controllers\Api\Auth\UserSubscriptionController@updateAutoRenew`) |
| `POST /account/lifecycle/subscribe` | `Route::post('subscribe',[UserSubscriptionController::class,'subscribeStagePlan']);` (Line 141 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserSubscriptionController@subscribeStagePlan` |

#### `frontend/pages/search.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /search` | `Route::get('/search', [SearchController::class, 'search']);` (Line 75) | `App\Http\Controllers\Api\SearchController@search` |

### Product Related APIs

(To be detailed after analyzing product-related files)

### Product Related APIs

#### `frontend/components/blog/BlogList.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /categories/{categorySlug}` | `Route::get('{category:url}', [CategoryController::class, 'show']);` (Line 95) | `App\Http\Controllers\Api\CategoryController@show` |
| `GET /blogs` | `Route::get('/', [PostApiController::class, 'index'])->name('api.posts.index');` (Line 213) | `App\Http\Controllers\Api\PostApiController@index` |

#### `frontend/pages/blogs/[url].vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /blogs/{url}` | `Route::get('/{post:url}', [PostApiController::class, 'show'])->name('api.posts.show');` (Line 214) | `App\Http\Controllers\Api\PostApiController@show` |

#### `frontend/pages/career/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /recruitment` | `Route::get('/', [RecruitmentController::class, 'index']);` (Line 170) | `App\Http\Controllers\Api\RecruitmentController@index` |

#### `frontend/pages/career/[url]/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /recruitment/{url}` | `Route::get('{recruitment:url}', [RecruitmentController::class, 'show']);` (Line 171) | `App\Http\Controllers\Api\RecruitmentController@show` |

#### `frontend/pages/cart/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php` or `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------------------------------------------------ | :-------------------------------------------------- |
| `useCart` composable | (See Cart Management section above) | (See Cart Management section above) |
| `GET /products/suggestions/cart` | (Assumed: No explicit route found, but likely a product-related controller) | `App\Http\Controllers\Api\ProductController@getCartSuggestions` (or similar) |
| `GET /account/addresses` | `Route::get('/addresses', [UserAddressController::class, 'getUserAllAddress'])->name('account.addresses.index');` (Line 58 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAddressController@getUserAllAddress` |
| `GET /account/addresses/{uuid}` | `Route::get('/addresses/{address:uuid}', [UserAddressController::class, 'show'])->name('account.addresses.show');` (Line 60 in `backend/routes/apis/user/account.php`) | `App\Http\Controllers\Api\Auth\UserAddressController@show` |
| `GET /integration/payment` | `Route::get('/payment', [IntegrationController::class, 'getPaymentIntegrations']);` (Line 160) | `App\Http\Controllers\Api\IntegrationController@getPaymentIntegrations` |
| `POST /order/place` | `Route::post('order/place', [OrderController::class, 'placeOrder'])->name('order.placed');` (Line 113) | `App\Http\Controllers\Api\OrderController@placeOrder` |

#### `frontend/pages/categories/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /categories/with-products` | `Route::get('/with-products', [CategoryController::class, 'getParentCategoriesWithProducts']);` (Line 94) | `App\Http\Controllers\Api\CategoryController@getParentCategoriesWithProducts` |

#### `frontend/pages/categories/[url].vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /products/sorts/get` | (Assumed: No explicit route found, but likely a product-related controller) | `App\Http\Controllers\Api\ProductController@getSortOptions` (or similar) |
| `GET /products/filters/get?category={categoryUrl}` | (Assumed: No explicit route found, but likely a product-related controller) | `App\Http\Controllers\Api\ProductController@getFilterOptions` (or similar) |
| `GET /categories/{categoryUrl}?{queryParams}` | `Route::get('{category:url}', [CategoryController::class, 'show']);` (Line 95) | `App\Http\Controllers\Api\CategoryController@show` |

#### `frontend/pages/dashboard/helpdesk/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /helpdesk/tickets` | `Route::get('tickets', [HelpDeskController::class, 'getAllTickets']);` (Line 200) | `App\Http\Controllers\Api\HelpDeskController@getAllTickets` |

#### `frontend/pages/dashboard/helpdesk/create.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /helpdesk/topics/ticket` | `Route::get('topics/ticket', [HelpDeskController::class, 'getTicketTopics']);` (Line 198) | `App\Http\Controllers\Api\HelpDeskController@getTicketTopics` |
| `POST /helpdesk/tickets` | `Route::post('tickets', [HelpDeskController::class, 'storeTicket']);` (Line 201) | `App\Http\Controllers\Api\HelpDeskController@storeTicket` |

#### `frontend/pages/dashboard/helpdesk/[url]/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /helpdesk/tickets/{uuid}` | `Route::get('tickets/{helpdesk:uuid}', [HelpDeskController::class, 'viewTicket']);` (Line 202) | `App\Http\Controllers\Api\HelpDeskController@viewTicket` |
| `POST /helpdesk/tickets/{uuid}/reply` | `Route::post('tickets/{helpdesk:uuid}/reply', [HelpDeskController::class, 'reply']);` (Line 203) | `App\Http\Controllers\Api\HelpDeskController@reply` |

#### `frontend/pages/dashboard/members/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/tree` | `Route::get('/tree',[\App\Http\Controllers\Api\Auth\UserStatsController::class, 'getUserTree']);` (Line 109) | `App\Http\Controllers\Api\Auth\UserStatsController@getUserTree` |

#### `frontend/pages/dashboard/orders/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /orders` | `Route::get('/', [OrderController::class, 'getAllOrders'])->name('orders.all');` (Line 116) | `App\Http\Controllers\Api\OrderController@getAllOrders` |

#### `frontend/pages/dashboard/orders/[uuid]/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /orders/{uuid}` | `Route::get('{order:uuid}', [OrderController::class, 'getOrderDetail']);` (Line 117) | `App\Http\Controllers\Api\OrderController@getOrderDetail` |
| `GET /orders/{uuid}/invoice` | `Route::get('{order:uuid}/invoice', [OrderController::class, 'getOrderInvoicePdf']);` (Line 120) | `App\Http\Controllers\Api\OrderController@getOrderInvoicePdf` |

#### `frontend/pages/dashboard/wallet/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /wallet` | `Route::get('/', [WalletController::class, 'index']);` (Line 40) | `App\Http\Controllers\Api\WalletController@index` |
| `GET /wallet/analytics?type={type}` | (Assumed: No explicit route found, but likely a wallet-related controller) | `App\Http\Controllers\Api\WalletController@getAnalytics` (or similar) |
| `POST /wallet/create` | `Route::post('create', [WalletController::class, 'create']);` (Line 41) | `App\Http\Controllers\Api\WalletController@create` |
| `POST /wallet/add-money` | `Route::post('add-money', [WalletController::class, 'addMoney']);` (Line 44) | `App\Http\Controllers\Api\WalletController@addMoney` |
| `POST /wallet/withdraw` | `Route::post('withdraw', [WalletController::class, 'withdraw']);` (Line 45) | `App\Http\Controllers\Api\WalletController@withdraw` |
| `POST /wallet/send` | `Route::post('send', [WalletController::class, 'send']);` (Line 46) | `App\Http\Controllers\Api\WalletController@send` |
| `POST /wallet/change-pin` | `Route::post('change-pin', [WalletController::class, 'changePin']);` (Line 47) | `App\Http\Controllers\Api\WalletController@changePin` |
| `POST /wallet/point-conversion` | `Route::post('point-conversion',[WalletController::class,'pointToBalanceConversion']);` (Line 50) | `App\Http\Controllers\Api\WalletController@pointToBalanceConversion` |

#### `frontend/pages/dashboard/wallet/beneficiary.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /beneficiaries` | `Route::get('/', [BeneficiaryController::class, 'index']);` (Line 54) | `App\Http\Controllers\Api\BeneficiaryController@index` |
| `POST /beneficiaries` | `Route::post('/', [BeneficiaryController::class, 'store']);` (Line 55) | `App\Http\Controllers\Api\BeneficiaryController@store` |
| `GET /beneficiaries/{account:uuid}` | `Route::get('{account:uuid}', [BeneficiaryController::class, 'show']);` (Line 56) | `App\Http\Controllers\Api\BeneficiaryController@show` |
| `PUT /beneficiaries/{account:uuid}` | `Route::put('{account:uuid}', [BeneficiaryController::class, 'update']);` (Line 57) | `App\Http\Controllers\Api\BeneficiaryController@update` |
| `DELETE /beneficiaries/{account:uuid}` | `Route::delete('{account:uuid}', [BeneficiaryController::class, 'destroy']);` (Line 58) | `App\Http\Controllers\Api\BeneficiaryController@destroy` |
| `POST /beneficiaries/{account:uuid}/default` | `Route::post('{account:uuid}/default', [BeneficiaryController::class, 'makeDefault']);` (Line 59) | `App\Http\Controllers\Api\BeneficiaryController@makeDefault` |

#### `frontend/pages/dashboard/wallet/transactions.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `GET /transactions` | `Route::get('/', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'index']);` (Line 153) | `App\Http\Controllers\Api\Transaction\TransactionDisplayController@index` |
| `GET /transactions/{uuid}` | `Route::get('/{transaction:uuid}', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'show']);` (Line 154) | `App\Http\Controllers\Api\Transaction\TransactionDisplayController@show` |
| `GET /transactions/{uuid}/request_pdf` | `Route::get('/{transaction:uuid}/request_pdf', [\App\Http\Controllers\Api\Transaction\TransactionDisplayController::class, 'sendInvoiceToEmail']);` (Line 155) | `App\Http\Controllers\Api\Transaction\TransactionDisplayController@sendInvoiceToEmail` |

### Product Related APIs

#### `backend/routes/apis/products.php`

| Frontend API Call | Backend Route (File: `backend/routes/apis/products.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /products` | `Route::get('/', [\App\Http\Controllers\Api\Product\ProductListController::class, 'index']);` (Line 8) | `App\Http\Controllers\Api\Product\ProductListController@index` |
| `GET /products/filters/get` | `Route::get('filters/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getFilterOptions']);` (Line 10) | `App\Http\Controllers\Api\Product\ProductListController@getFilterOptions` |
| `GET /products/sorts/get` | `Route::get('sorts/get', [\App\Http\Controllers\Api\Product\ProductListController::class, 'getSortingOptions']);` (Line 11) | `App\Http\Controllers\Api\Product\ProductListController@getSortingOptions` |
| `GET /products/bestSaleProducts` | `Route::get('bestSaleProducts', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'bestSaleProducts']);` (Line 14) | `App\Http\Controllers\Api\Product\ProductDisplayController@bestSaleProducts` |
| `GET /products/trendingProducts` | `Route::get('trendingProducts', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'trendingProducts']);` (Line 15) | `App\Http\Controllers\Api\Product\ProductDisplayController@trendingProducts` |
| `GET /products/{product:url}` | `Route::get('{product:url}', [\App\Http\Controllers\Api\Product\ProductDisplayController::class, 'show']);` (Line 18) | `App\Http\Controllers\Api\Product\ProductDisplayController@show` |

### User Account & Profile

#### `frontend/pages/dashboard/index.vue`

| Frontend API Call | Backend Route (File: `backend/routes/apis/user/account.php`) | Backend Controller & Method |
| :---------------- | :------------------------------------------------------ | :-------------------------------------------------- |
| `GET /account/stats/dashboard` | `Route::get('stats/dashboard',[\App\Http\Controllers\Api\Auth\UserDashboardController::class,'getAccountDashboard']);` (Line 130) | `App\Http\Controllers\Api\Auth\UserDashboardController@getAccountDashboard` |

### Other Pages/Components

(To be detailed after analyzing other pages and components)

### Other Pages/Components

#### `frontend/pages/contact.vue`

| Frontend API Call | Backend Route (File: `backend/routes/api.php`) | Backend Controller & Method |
| :---------------- | :--------------------------------------------- | :-------------------------- |
| `POST /contact/user` | `Route::post('/contact/user', [\App\Http\Controllers\Api\InquiryController::class, 'storeUser']);` (Line 209) | `App\Http\Controllers\Api\InquiryController@storeUser` |
| `POST /contact/business` | `Route::post('/contact/business', [\App\Http\Controllers\Api\InquiryController::class, 'storeBusiness']);` (Line 210) | `App\Http\Controllers\Api\InquiryController@storeBusiness` |

#### `frontend/pages/dashboard/account/insights/index.vue`

This page dynamically loads insight components based on the user's role. The individual insight components (`ApplicantInsight.vue`, `MemberInsight.vue`, `OrganizerInsight.vue`, `RegularInsight.vue`) currently use static data for their displays and charts. These should be integrated with backend APIs to fetch real-time data.

## Unused Backend API Routes

(To be detailed after a comprehensive analysis of frontend API usage)

## Static Data to API Conversion

(To be detailed after identifying static data in the frontend)

## Static Data to API Conversion

The following frontend components currently rely on static, hardcoded data. For a dynamic and real-time user experience, these components should be updated to fetch their data from dedicated backend API endpoints.

-   **`frontend/components/insights/organizer/OrganizerInsight.vue`**:
    -   Currently uses static `stats` (members joined, active recruits, commission, rank) and `rawData` for charts.
    -   **Suggested API:** `GET /account/insights/organizer` (or similar) to fetch performance statistics and chart data for organizers/mentors.
-   **`frontend/components/insights/regular/RegularInsight.vue`**:
    -   Currently uses static `stats` (products ordered, services ordered, total spending, free features used) and `rawData` for charts.
    -   **Suggested API:** `GET /account/insights/regular` (or similar) to fetch activity and spending statistics for regular users.
-   **`frontend/components/insights/applicant/ApplicantInsight.vue`**:
    -   Currently displays static content related to career opportunities.
    -   **Suggested API:** `GET /account/insights/applicant` (or similar) to fetch application status, job updates, or career path information for applicants.
-   **`frontend/pages/about.vue`**:
    -   Currently uses static `missionValues`, `services`, `operations`, `leadership`, and `milestones` arrays.
    -   **Suggested API:** Dedicated endpoints for fetching company information, such as `/about/mission`, `/about/services`, `/about/operations`, `/about/team`, and `/about/milestones`.
-   **`frontend/components/home/AffiliateBenefitsSection.vue`**:
    -   Currently uses a static `benefits` array.
    -   **Suggested API:** `GET /home/affiliate-benefits` (or similar) to fetch dynamic affiliate program benefits.
-   **`frontend/pages/terms.vue`**:
    -   Currently uses static `sections` array, `effectiveDate`, and `lastUpdated`.
    -   **Suggested API:** `GET /pages/terms` (or similar) to fetch dynamic terms and conditions content, including sections and dates.
-   **`frontend/pages/career/index.vue`**:
    -   Currently uses static `heroHighlights`, `enhancedValues`, and `enhancedLifeAtCompany` arrays.
    -   **Suggested API:** Dedicated endpoints for fetching career page content, such as `/career/highlights`, `/career/values`, and `/career/life-at-company`.

## Unused Backend API Routes

Based on the current frontend analysis, the following backend API routes do not appear to be directly called by the existing frontend implementation. These routes might be intended for future features, internal use, or other client applications.

-   `POST /tokens/create` (`App\Http\Controllers\Api\Auth\AuthController@storeToken`)
-   `POST /tokens/delete` (`App\Http\Controllers\Api\Auth\AuthController@destroyToken`)
-   `GET /user` (`App\Http\Controllers\Api\Auth\SanctumUserController@getUser`)
-   `GET /account/applications` (`App\Http\Controllers\Api\Auth\JobApplicationController@index`)
-   `GET /account/applications/{application:uuid}` (`App\Http\Controllers\Api\Auth\JobApplicationController@show`)
-   `POST /account/apply/{recruitment:url}` (`App\Http\Controllers\Api\Auth\JobApplicationController@apply`)
-   `GET /account/stats/minimal` (`App\Http\Controllers\Api\Auth\UserStatsController@getMinimal`)
-   `GET /account/stats/member/{user:uuid}` (`App\Http\Controllers\Api\Auth\UserStatsController@getMemberStat`)
-   `GET /account/notifications` (`App\Http\Controllers\Api\Auth\UserNotificationController@index`)
-   `POST /account/notifications/{id}/read` (`App\Http\Controllers\Api\Auth\UserNotificationController@markAsRead`)
-   `POST /account/notifications/{id}/unread` (`App\Http\Controllers\Api\Auth\UserNotificationController@markAsUnread`)
-   `POST /account/notifications/mark-all-read` (`App\Http\Controllers\Api\Auth\UserNotificationController@markAllRead`)
-   `DELETE /account/notifications/{id}` (`App\Http\Controllers\Api\Auth\UserNotificationController@destroy`)
-   `DELETE /account/notifications/clear-all` (`App\Http\Controllers\Api\Auth\UserNotificationController@clearAll`)
-   `GET /account/lifecycle` (`App\Http\Controllers\Api\Auth\UserLifecycleController@getUserLifecycleProgress`)
-   `GET /account/lifecycle/stages` (`App\Http\Controllers\Api\Auth\UserLifecycleController@getAllStages`)
-   `GET /account/lifecycle/stages/{stage:url}` (`App\Http\Controllers\Api\Auth\UserLifecycleController@showStage`)
-   `GET /account/lifecycle/level/{level:url}` (`App\Http\Controllers\Api\Auth\UserLifecycleController@getLevel`)
-   `GET /pages` (`App\Http\Controllers\Api\PageController@getPages`)
-   `GET /categories` (`App\Http\Controllers\Api\CategoryController@index`)
-   `GET /product/engagements/{product:url}` (`App\Http\Controllers\Api\Product\ProductEngagementController@index`)
-   `POST /product/engagement/{product:url}` (`App\Http\Controllers\Api\Product\ProductEngagementController@store`)
-   `PUT /product/engagement/{product_engagement}` (`App\Http\Controllers\Api\Product\ProductEngagementController@update`)
-   `DELETE /product/engagement/{product_engagement}` (`App\Http\Controllers\Api\Product\ProductEngagementController@destroy`)
-   `POST /product/engagement/{product_engagement}/helpfull` (`App\Http\Controllers\Api\Product\ProductEngagementController@helpFullEngagement`)
-   `GET /flash-deals` (`App\Http\Controllers\Api\FlashDealController@index`)
-   `GET /flash-deals/stats` (`App\Http\Controllers\Api\FlashDealController@getStats`)
-   `GET /flash-deals/categories` (`App\Http\Controllers\Api\FlashDealController@getCategories`)
-   `GET /order/insight` (`App\Http\Controllers\Api\OrderController@getInsight`)
-   `POST /orders/{order:uuid}/canceled` (`App\Http\Controllers\Api\OrderController@cancelOrder`)
-   `POST /orders/{order:uuid}/return` (`App\Http\Controllers\Api\OrderController@returnOrder`)
-   `POST /orders/{order:uuid}/refund` (`App\Http\Controllers\Api\OrderController@refundOrder`)
-   `GET /_transaction/validate/{transaction:uuid}` (`App\Http\Controllers\Api\Transaction\TransactionActionController@confirmTransaction`)
-   `GET /_transaction/failed/{transaction:uuid}` (`App\Http\Controllers\Api\Transaction\TransactionActionController@failureTransaction`)
-   `GET /lifecycle/timeline` (`App\Http\Controllers\Api\LifecycleController@getTimeline`)
-   `GET /lifecycle/stages` (`App\Http\Controllers\Api\LifecycleController@getAllStages`)
-   `GET /lifecycle/stage/{stage:url}` (`App\Http\Controllers\Api\LifecycleController@getStage`)
-   `GET /lifecycle/level/{level:url}` (`App\Http\Controllers\Api\LifecycleController@getLevel`)
-   `GET /sales` (`App\Http\Controllers\Api\SaleController@index`)
-   `GET /helpdesk/topics/faq` (`App\Http\Controllers\Api\HelpDeskController@getFaqTopics`)
-   `POST /helpdesk/tickets/{helpdesk:uuid}/attachments` (`App\Http\Controllers\Api\HelpDeskController@uploadAttachment`)
-   `GET /push/vapid-public-key` (`App\Http\Controllers\Api\PushNotificationController@getVapidPublicKey`)
-   `POST /push/subscribe` (`App\Http\Controllers\Api\PushNotificationController@subscribe`)
-   `POST /push/unsubscribe` (`App\Http\Controllers\Api\PushNotificationController@unsubscribe`)
-   `POST /push/send-to-user` (`App\Http\Controllers\Api\PushNotificationController@sendToUser`)
-   `POST /push/send-to-all` (`App\Http\Controllers\Api\PushNotificationController@sendToAll`)
-   `POST /push/send-to-level` (`App\Http\Controllers\Api\PushNotificationController@sendToLevel`)
-   `GET /products` (`App\Http\Controllers\Api\Product\ProductListController@index`)
-   `GET /products/bestSaleProducts` (`App\Http\Controllers\Api\Product\ProductDisplayController@bestSaleProducts`)
-   `GET /products/trendingProducts` (`App\Http\Controllers\Api\Product\ProductDisplayController@trendingProducts`)

## Full Integration Workflow

The frontend and backend integration follows a standard API-driven architecture, primarily utilizing Laravel Sanctum for authentication and Nuxt.js's `useSanctumFetch` composable for making API requests.

1.  **Authentication Flow:**
    *   User registration, login, and password recovery are handled via dedicated API endpoints (`/register`, `/login`, `/auth/send-otp`, `/auth/verify-otp`, `/reset_password`).
    *   Laravel Sanctum manages API token creation and validation, ensuring secure communication for authenticated users.
    *   Guest users are managed through `X-Guest-ID` and `X-Guest-Token` headers for cart and other non-authenticated functionalities.

2.  **Data Fetching and Manipulation:**
    *   Frontend components and pages make `GET`, `POST`, `PUT`, `DELETE` requests to specific backend API endpoints to retrieve, create, update, and delete data.
    *   `useSanctumFetch` automatically handles attaching authentication headers (Sanctum token or guest credentials) to requests.
    *   Query parameters are extensively used for filtering, sorting, and pagination of data (e.g., `/orders?page=1&status=pending`).

3.  **Error Handling:**
    *   API responses are typically structured to include `data`, `message`, and `errors` fields.
    *   Frontend components catch API errors and display user-friendly messages using the `useToast` composable.
    *   Specific error handling for authentication issues (e.g., 401, 403, 419 status codes) is implemented to refresh guest credentials or prompt re-authentication.

4.  **Data Transformation and Display:**
    *   Backend API responses are often transformed on the frontend to fit the UI requirements (e.g., formatting dates, currencies, mapping data to chart formats).
    *   Computed properties and helper functions are used to process and display data effectively.

5.  **Static Data Integration (Future Work):**
    *   Components currently displaying static data (e.g., `about.vue`, `privacy.vue`, `shipping.vue`, `terms.vue`, insight components, career page content) should be refactored to fetch this content from dedicated backend API endpoints.
    *   This would allow for dynamic content management from the backend, improving flexibility and maintainability.

6.  **Component-Based API Interaction:**
    *   API calls are encapsulated within composables (e.g., `useCart`, `useWishlist`) or directly within components/pages, promoting reusability and separation of concerns.

This workflow ensures a clear separation between frontend presentation and backend logic, facilitating scalable and maintainable application development.

(To be detailed after a comprehensive analysis)
