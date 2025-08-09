<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// LOGGED IN USER FUNCTIONALITIES

Route::get('/',[\App\Http\Controllers\Api\Auth\SanctumUserController::class,'getUser']);

Route::post('/change-avatar', [\App\Http\Controllers\Api\Auth\SanctumUserController::class, 'updateAvatar']);
Route::post('/change-profile', [\App\Http\Controllers\Api\Auth\SanctumUserController::class, 'updateProfile']);
Route::post('/change-password', [\App\Http\Controllers\Api\Auth\SanctumUserController::class, 'updatePassword']);

Route::post('/onboarding',[\App\Http\Controllers\Api\Auth\SanctumUserController::class,'processOnboarding']);
