<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
Route::group(['middleware' => ['throttle:60,1']], function () {
    Route::post('send-otp', 'UserController@sendOTP');
    Route::post('verify-otp', 'UserController@verifyOTP');
    Route::post('get-interest-hobbies', 'UserController@getInterestHobbies');
    Route::post('static-content', 'UserController@getStaticPage');
    
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('get-profile', 'UserController@getProfile');
        Route::post('add-first-image', 'UserController@userImage');
        Route::post('add-user-image', 'UserController@userMultipalImage');
        Route::post('dating-submit', 'UserController@datingSubmit');
        Route::post('add-connect', 'UserController@addConnect');
        
    });
});