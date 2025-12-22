<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// use prefix "geo/"

Route::get('/countries',[\App\Http\Controllers\Api\GeoLocationController::class,'getAllCountries']);
Route::get('/country/{country:iso_code_2}',[\App\Http\Controllers\Api\GeoLocationController::class,'getCountry']);

Route::get('/states/{country:iso_code_2}',[\App\Http\Controllers\Api\GeoLocationController::class,'getAllStatesOfACountry']);
Route::get('/state/{state:code}',[\App\Http\Controllers\Api\GeoLocationController::class,'getState']);
