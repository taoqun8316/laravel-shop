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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

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

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('user_addresses', 'UserAddressesController@index');
    Route::post('user_addresses', 'UserAddressesController@store');

});


