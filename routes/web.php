<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Authentication routes

use App\Http\Controllers\Auth\GitHubSocialiteController;
use App\Http\Controllers\Auth\WeixinWebSocialiteController;

Auth::routes();

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

// Include Wave Routes
Wave::routes();

Route::middleware('guest')->group(function () {
    Route::get('auth/github/redirect', [GitHubSocialiteController::class, 'redirect'])->name('github.login');
    Route::get('auth/github/callback', [GitHubSocialiteController::class, 'callback']);

    Route::get('auth/weixin/redirect', [WeixinWebSocialiteController::class, 'redirect'])->name('weixinweb.login');
    Route::get('auth/weixin/callback', [WeixinWebSocialiteController::class, 'callback']);
});
