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


    Route::post('/export-data', [UserAccountController::class, 'exportData']);
    Route::delete('/delete', [UserAccountController::class, 'deleteAccount']);



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
    Route::get('/addresses/{address:uuid}', [UserAddressController::class, 'show'])->name('account.addresses.show');
    Route::put('/addresses/{address:uuid}', [UserAddressController::class, 'updateUserAddress'])->name('account.addresses.update');



    /**
     *  Prefix 'account'
     *  Full Prefix: /account/kyc
     * KYC
     */
    Route::get('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'getUserKyc']);
    Route::post('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'addUserKyc']);
    Route::put('/kyc',[\App\Http\Controllers\Api\Auth\UserKycController::class,'updateUserKyc']);



    /**
     * Prefix 'account'
     * Full Prefix: /account/application
     * Job Applications
     */

    Route::prefix('/applications')->group(function (){
        Route::get('/', [\App\Http\Controllers\Api\Auth\JobApplicationController::class, 'index']);
        Route::get('/{application:uuid}', [\App\Http\Controllers\Api\Auth\JobApplicationController::class, 'show']);
    })->middleware('auth:sanctum');


    Route::prefix('stats')->group(function (){

        Route::get('/',[\App\Http\Controllers\Api\Auth\UserStatsController::class,'index']);
        Route::get('/minimal',[\App\Http\Controllers\Api\Auth\UserStatsController::class,'getMinimal']);
        Route::get('/member/{user:uuid}',[\App\Http\Controllers\Api\Auth\UserStatsController::class,'getMemberStat']);

    });




});
