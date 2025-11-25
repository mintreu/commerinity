<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/v1')->group(base_path('routes/v1/version_one_api_routes.php'));


Route::prefix('/v2')->group(base_path('routes/v2/version_two_api_routes.php'));
