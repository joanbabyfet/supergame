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

Route::post('check', 'ctl_common@check')->name('api.common.check');

Route::group(['middleware' => ['set_locale', 'check_sign']], function() {
    Route::post('player/login', 'ctl_user@login')->name('api.user.login');
    Route::post('player/logout', 'ctl_user@logout')->name('api.user.logout');
    Route::post('player/create', 'ctl_user@register')->name('api.user.register');
    Route::post('player/deposit', 'ctl_user@deposit')->name('api.user.deposit');
    Route::post('player/withdraw', 'ctl_user@withdraw')->name('api.user.withdraw');
    Route::post('player/balance', 'ctl_user@get_balance')->name('api.user.get_balance');
    Route::post('player/is_online', 'ctl_user@is_online')->name('api.user.is_online');
    Route::post('agent/balance', 'ctl_agent@get_agent_balance')->name('api.agent.get_agent_balance');
    Route::post('check_order', 'ctl_order_transfer@check_order')->name('api.order_transfer.check_order');
    Route::post('get_report', 'ctl_report@index')->name('api.report.index');
    Route::post('upload', 'ctl_upload@upload')->name('api.upload.upload');
});
