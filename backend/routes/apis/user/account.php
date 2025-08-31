<?php

use App\Http\Controllers\Api\Auth\SanctumUserController;
use App\Http\Controllers\Api\Auth\UserAccountController;
use App\Http\Controllers\Api\Auth\UserOnboardingController;
use App\Http\Controllers\Api\Auth\UserSubscriptionController;
use App\Http\Controllers\Api\Auth\UserAddressController;
use Illuminate\Support\Facades\Route;

/**
 * Prefix of all route group is '/account'
 */

Route::middleware('auth:sanctum')->group(function () {
    /**
     * Account info
     */
    Route::get('/', [SanctumUserController::class, 'getUser'])->name('account.show');

    /**
     * prefix '/account'
     * Profile
     */
    Route::get('/profile', [SanctumUserController::class, 'getProfile'])->name('account.profile.show');
    Route::put('/profile', [UserAccountController::class, 'updateProfile'])->name('account.profile.update');

    /**
     * use bse api endpoint with prefix '/'
     * to send and validate 6 digit otp
     * Contact
     */

    Route::put('/contact', [UserAccountController::class, 'updateContact'])->name('account.contact.update');


    /**
     * Avatar
     */
    Route::put('/avatar', [UserAccountController::class, 'updateAvatar'])->name('account.avatar.update');

    /**
     * Prefix ('/account')
     * Password
     */
    Route::put('/password', [UserAccountController::class, 'updatePassword'])->name('account.password.update');

    /**
     * prefix ('/account'))
     * Onboarding
     */
    Route::post('/onboarding', [UserOnboardingController::class, 'processOnboarding'])->name('account.onboarding.store');

    /**
     * Subscription
     */
    Route::get('/subscription', [UserSubscriptionController::class, 'getCurrentSubscription'])->name('account.subscription.show');
    Route::post('/subscription', [UserSubscriptionController::class, 'subscribeSubscription'])->name('account.subscription.store');

    /**
     * Addresses
     */
    Route::get('/addresses', [UserAddressController::class, 'getUserAllAddress'])->name('account.addresses.index');
    Route::post('/addresses', [UserAddressController::class, 'addUserAddress'])->name('account.addresses.store');
    Route::put('/addresses/{address:uuid}', [UserAddressController::class, 'updateUserAddress'])->name('account.addresses.update');



    /**
     * KYC
     */
    Route::get('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'getUserKyc']);
    Route::post('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'addUserKyc']);
    Route::post('/put',[\App\Http\Controllers\Api\Auth\UserKycController::class,'updateUserKyc']);



});
