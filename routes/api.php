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
    Route::apiResource('restrictions', 'restriction_controller');
    Route::put('passrestore/{user}', 'user_controller@passrestore');
    Route::put('passedit/{user}', 'user_controller@passedit');
    Route::post('usageimport', 'usage_controller@import');
    Route::get('showAppLocations/{app_id}', 'usage_controller@showAppLocations');
    Route::get('showUseLocations', 'usage_controller@showUseLocations');
    Route::get('showLastUsesLocations', 'usage_controller@showLastUsesLocations');
    Route::get('showAppUse/{app_id}', 'usage_controller@showAppUse');
    Route::get('showAllAppUseToday', 'usage_controller@showAllAppUseToday');
    Route::get('showAllAppUseThisWeek', 'usage_controller@showAllAppUseThisWeek');
    Route::get('showAllAppUseThisMonth', 'usage_controller@showAllAppUseThisMonth');
    Route::get('showAllTimeAppUse/{app_id}', 'usage_controller@showAllTimeAppUse');
});
