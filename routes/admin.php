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
Route::get(
    'cache-clear',
    function () {
        \Artisan::call('config:cache');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        return redirect()->back();
    }
);
Route::group(['middleware' => ['throttle:60,1', 'cors']], function () {
    Route::post('login', 'UserController@login');
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('dashboard', 'UserController@dashboard');
        Route::get('get-profile', 'UserController@getProfile');
        Route::post('store-admin', 'UserController@storeAdmin');

        // User Routes
        Route::get('user-list', 'UserController@userListing');
        Route::post('user-status', 'UserController@userStatus');
        
        // Category Routes
        Route::get('category-list', 'CategoryController@categoryListing');
        Route::post('category-store', 'CategoryController@categoryStore');
        Route::post('category-update', 'CategoryController@categoryUpdate');
        Route::post('category-delete', 'CategoryController@categoryDelete');
        //Static Page
        Route::get('static-page', 'ContentController@getStaticPage');
        Route::post('static-page-update', 'ContentController@staticPageUpdate');
        
        //Report User Routes
        Route::get('report-user-list', 'UserController@reportUserListing');
        Route::post('report-user-block', 'UserController@reportUserBlocked');
        
    });
});