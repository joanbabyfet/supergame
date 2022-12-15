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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['middleware' => ['set_locale']], function() {
    //Route::get('marquee', 'ctl_marquee@index')->name('client.marquee.index');

    //目前改用jwt_auth
    Route::group(['middleware' => ['assign_guard:client', 'jwt_auth:client', 'set_locale']], function() {
        Route::get('game_table_history', 'ctl_game_table@history')->name('client.game_table.history');
        Route::get('game_table', 'ctl_game_table@index')->name('client.game_table.index');
        Route::get('winloss', 'ctl_winloss@index')->name('client.winloss.index');
        Route::get('get_table_user_list', 'ctl_winloss@get_table_user_list')->name('client.winloss.get_table_user_list');
    });
});
