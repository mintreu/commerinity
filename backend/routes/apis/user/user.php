<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SanctumUserController;
use App\Http\Controllers\Api\Auth\UserAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// LOGGED IN USER FUNCTIONALITIES





Route::get('/',[\App\Http\Controllers\Api\Auth\SanctumUserController::class,'getUser']);
Route::get('/my-profile',[\App\Http\Controllers\Api\Auth\SanctumUserController::class,'getProfile']);
Route::post('/change-avatar', [\App\Http\Controllers\Api\Auth\UserAccountController::class, 'updateAvatar']);
Route::post('/change-profile', [\App\Http\Controllers\Api\Auth\UserAccountController::class, 'updateProfile']);
Route::post('/change-password', [\App\Http\Controllers\Api\Auth\UserAccountController::class, 'updatePassword']);

Route::post('/onboarding',[\App\Http\Controllers\Api\Auth\UserOnboardingController::class,'processOnboarding']);

Route::post('/get-subscription',[\App\Http\Controllers\Api\Auth\UserSubscriptionController::class,'getCurrentSubscription']);

Route::post('/subscribe-now',[\App\Http\Controllers\Api\Auth\UserSubscriptionController::class,'subscribeSubscription']);

Route::get('/address-all',[\App\Http\Controllers\Api\Auth\UserAddressController::class,'getUserAllAddress']);
Route::post('/address/create',[\App\Http\Controllers\Api\Auth\UserAddressController::class,'addUserAddress']);
Route::put('/address/{address:uuid}',[\App\Http\Controllers\Api\Auth\UserAddressController::class,'updateUserAddress']);
