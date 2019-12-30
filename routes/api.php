<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', 'user_controller@login');
Route::post('register', 'user_controller@store');

Route::post('appsimport', 'app_controller@import');


Route::middleware(['Checkout'])->group(function(){
    Route::apiResource('users', 'user_controller');
    Route::put('passrestore/{user}', 'user_controller@passrestore');
    Route::put('passedit/{user}', 'user_controller@passedit');
    Route::post('usageimport', 'usage_controller@import');
});

