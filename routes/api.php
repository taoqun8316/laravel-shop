<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/register', function(){
    \App\Models\User::create([
        'name' => 'bb',
        'email' => 'test@bb.com',
        'password' => \Illuminate\Support\Facades\Hash::make('123456'),
    ]);
});

Route::group(['prefix' => 'auth'], function(){
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('user_addresses', 'UserAddressesController@index');
    Route::post('user_addresses', 'UserAddressesController@store');
    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit');
    Route::post('user_addresses/{user_address}', 'UserAddressesController@update');
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy');

    Route::get('products/favorites', 'ProductsController@favorites');
    Route::post('products/{product}/favorite', 'ProductsController@favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor');

    Route::post('cart', 'CartController@add');
    Route::get('cart', 'CartController@index');
    Route::delete('cart/{sku}', 'CartController@remove');

    Route::post('orders', 'OrdersController@store');
    Route::get('orders', 'OrdersController@index');
    Route::get('orders/{order}', 'OrdersController@show');

    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay');
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
    Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');

});

Route::get('products/{product}', 'ProductsController@show');
Route::get('products', 'ProductsController@index');

Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
