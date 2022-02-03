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



});

