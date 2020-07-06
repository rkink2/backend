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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'api',
], function ($router){
    Route::post('login', 'Auth\ApiAuthController@login');
    Route::post('register','Auth\ApiAuthController@register');
    Route::post('user/activation', 'Auth\ApiAuthController@userActivation');
    Route::post('sendPasswordResetLink', 'Auth\ApiAuthController@forgotPassword');
    Route::post('resetPassword', 'Auth\ApiAuthController@resetPassword');

    Route::group(
        ['prefix' => 'users'], function (){
        Route::get('/get', 'Api\UserManagementController@index');
        Route::post('/saveUser', 'Api\UserManagementController@saveUser');
        Route::get('/delete', 'Api\UserManagementController@deleteUser');
        Route::get('/getUser', 'Api\UserManagementController@getUser');
    });
});
