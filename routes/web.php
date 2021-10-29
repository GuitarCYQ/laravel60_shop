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


Route::namespace('Web\Merchant')
//    ->middleware('web')
    ->group(function () {
        #商户登录
        Route::post('merchant/merchantLogin', 'MerchantLoginController@login');
        #商户退出
        Route::post('merchant/merchantLogout', 'MerchantLoginController@logout');
    });
