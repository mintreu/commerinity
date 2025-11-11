<?php
use Illuminate\Support\Facades\Route;

Route::prefix('stats')->group(function (){
    Route::get('homepage', [\App\Http\Controllers\Api\StatsController::class, 'getHomepageStats']);
    Route::get('stores/hero', [\App\Http\Controllers\Api\StatsController::class, 'getHeroStats']);
    Route::get('categories', [\App\Http\Controllers\Api\StatsController::class, 'getCategoryStats']);
});
