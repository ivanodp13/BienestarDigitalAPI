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
Route::put('passrestore', 'user_controller@passrestore');


Route::middleware(['Checkout'])->group(function(){
    Route::apiResource('users', 'user_controller');
    Route::post('restrictions/{app_id}', 'restriction_controller@store');
    Route::put('passedit', 'user_controller@passedit');
    Route::get('showUserData', 'user_controller@showUserInfo');
    Route::post('usageimport', 'usage_controller@import');
    Route::get('showUseLocations', 'usage_controller@showUseLocations');
    Route::get('showAllAppUseToday', 'usage_controller@showAllAppUseToday');
    Route::get('showAllAppUseThisWeek', 'usage_controller@showAllAppUseThisWeek');
    Route::get('showAllAppUseThisMonth', 'usage_controller@showAllAppUseThisMonth');
    Route::get('appUseDetails/{appName}', 'usage_controller@appUseDetails');
});


