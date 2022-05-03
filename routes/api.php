<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function(){
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::get('/register', function(){
    \App\Models\User::create([
        'name' => 'bb',
        'email' => 'test@bb.com',
        'password' => \Illuminate\Support\Facades\Hash::make('123456'),
    ]);
});

Route::get('products', 'ProductsController@index')->name("products.index");
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify');


Route::group(['middleware' => ['jwt.auth']], function() {
    Route::get('user_addresses', 'UserAddressesController@index');
    Route::post('user_addresses', 'UserAddressesController@store');
    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit');
    Route::post('user_addresses/{user_address}', 'UserAddressesController@update');
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@delete');

    Route::get('products/favorites', 'ProductsController@favorites');
    Route::get('products/{product}', 'ProductsController@show');
    Route::post('products/{product}/favorite', 'ProductsController@favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor');

    Route::post('cart', 'CartController@add');
    Route::get('cart', 'CartController@index');
    Route::delete('cart/{sku}', 'CartController@remove');

    Route::post('orders', 'OrdersController@store');
    Route::get('orders', 'OrdersController@index');
    Route::get('orders/{order}', 'OrdersController@show');
    Route::post('orders/{order}/received', 'OrdersController@received');
    Route::get('orders/{order}/review', 'OrdersController@review');
    Route::post('orders/{order}/review', 'OrdersController@sendReview');
    Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund');

    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay');
    Route::get('payment/{order}/wechat', 'PaymentController@payByWechat');
    Route::post('payment/wechat/notify', 'PaymentController@wechatNotify');
    Route::post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify');

    Route::get('coupon_codes/{code}', 'CouponCodesController@show');

});


