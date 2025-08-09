<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::post('/tokens/create',[AuthController::class,'storeToken'])->middleware(['guest']);
Route::post('/tokens/delete',[AuthController::class,'destroyToken'])->middleware(['auth:sanctum']);

Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout'])->middleware(['auth:sanctum']);
Route::post('/auth/has_contact',[AuthController::class,'checkContactExistence']);
Route::post('/auth/send-otp',[AuthController::class,'sendOtp']);
Route::post('/auth/verify-otp',[AuthController::class,'verifyOtp']);
Route::post('register',[AuthController::class,'register']);
Route::post('reset_password',[AuthController::class,'resetPassword']);
