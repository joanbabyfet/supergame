<?php

use Illuminate\Support\Facades\Route;

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

//为路由指定名称, 才能使用route函数, 例 route('admin.common.ping') 生成地址为 {{host}}/ping
Route::get('ping', 'ctl_common@ping')->name('admin.common.ping');
Route::get('ip', 'ctl_common@ip')->name('admin.common.ip');
Route::get('get_captcha', 'ctl_common@get_captcha')->name('admin.common.get_captcha');
Route::get('reload_captcha', 'ctl_common@reload_captcha')->name('admin.common.reload_captcha');

Route::group(['middleware' => ['ip_filter']], function() {
    Route::post('login', 'ctl_index@login')->name('admin.index.login');
    //Route::post('refresh_token', 'ctl_index@refresh_token')->name('admin.index.refresh_token'); //刷新认证token
    Route::get('telegram/webhook', 'ctl_telegram@webhook')->name('admin.telegram.webhook');

    //目前改用jwt_auth
    Route::group(['middleware' => ['assign_guard:admin', 'jwt_auth:admin', 'safe_ips']], function() {
        Route::get('/', 'ctl_index@index')->name('admin.index.index'); //首页
        Route::post('logout', 'ctl_index@logout')->name('admin.index.logout'); //退出
        Route::get('userinfo', 'ctl_index@detail')->name('admin.index.detail'); //用户信息
        Route::post('edit_pwd', 'ctl_index@edit_pwd')->name('admin.index.edit_pwd'); //修改用户密码
        Route::get('test', 'ctl_test@index')->name('admin.test.index');
        Route::get('get_menu', 'ctl_index@get_menu')->name('admin.index.get_menu');
        Route::get('permission_list', 'ctl_role@permission_list')->name('admin.role.permission_list');
        Route::get('get_role_options', 'ctl_common@get_role_options')->name('admin.common.get_role_options');
        Route::get('get_room_options', 'ctl_common@get_room_options')->name('admin.common.get_room_options');
        Route::get('get_agent_options', 'ctl_common@get_agent_options')->name('admin.common.get_agent_options');
        Route::get('get_module_options', 'ctl_common@get_module_options')->name('admin.common.get_module_options');
        Route::get('get_op_agent_options', 'ctl_common@get_op_agent_options')->name('admin.common.get_op_agent_options');
        Route::get('get_op_admin_options', 'ctl_common@get_op_admin_options')->name('admin.common.get_op_admin_options');
        Route::get('get_user_options', 'ctl_common@get_user_options')->name('admin.common.get_user_options');
        Route::post('upload', 'ctl_upload@upload')->name('admin.upload.upload');
        Route::post('telegram/set_webhook', 'ctl_telegram@set_webhook')->name('admin.telegram.set_webhook');
        Route::post('telegram/delete_webhook', 'ctl_telegram@delete_webhook')->name('admin.telegram.delete_webhook');
        Route::post('telegram/get_webhook_info', 'ctl_telegram@get_webhook_info')->name('admin.telegram.get_webhook_info');
        Route::post('telegram/get_me', 'ctl_telegram@get_me')->name('admin.telegram.get_me');
        Route::post('telegram/send', 'ctl_telegram@send')->name('admin.telegram.send');

        Route::group(['middleware' => ['permission:admin']], function (){
            Route::get('example', 'ctl_example@index')->name('admin.example.index');
            Route::get('example/detail', 'ctl_example@detail')->name('admin.example.detail');
            Route::post('example/add', 'ctl_example@add')->name('admin.example.add');
            Route::post('example/edit', 'ctl_example@edit')->name('admin.example.edit');
            Route::post('example/delete', 'ctl_example@delete')->name('admin.example.delete');
            Route::post('example/enable', 'ctl_example@enable')->name('admin.example.enable');
            Route::post('example/disable', 'ctl_example@disable')->name('admin.example.disable');
            Route::get('example/export', 'ctl_example@export')->name('admin.example.export');
            Route::get('example_cache', 'ctl_example_cache@index')->name('admin.example_cache.index');
            Route::get('example_cache/detail', 'ctl_example_cache@detail')->name('admin.example_cache.detail');
            Route::post('example_cache/add', 'ctl_example_cache@add')->name('admin.example_cache.add');
            Route::post('example_cache/edit', 'ctl_example_cache@edit')->name('admin.example_cache.edit');
            Route::post('example_cache/delete', 'ctl_example_cache@delete')->name('admin.example_cache.delete');
            Route::post('example_cache/enable', 'ctl_example_cache@enable')->name('admin.example_cache.enable');
            Route::post('example_cache/disable', 'ctl_example_cache@disable')->name('admin.example_cache.disable');
            Route::get('example_cache/export', 'ctl_example_cache@export')->name('admin.example_cache.export');
            Route::get('example_redis', 'ctl_example_redis@index')->name('admin.example_redis.index');
            Route::get('example_redis/detail', 'ctl_example_redis@detail')->name('admin.example_redis.detail');
            Route::post('example_redis/add', 'ctl_example_redis@add')->name('admin.example_redis.add');
            Route::post('example_redis/edit', 'ctl_example_redis@edit')->name('admin.example_redis.edit');
            Route::post('example_redis/delete', 'ctl_example_redis@delete')->name('admin.example_redis.delete');
            Route::post('example_redis/enable', 'ctl_example_redis@enable')->name('admin.example_redis.enable');
            Route::post('example_redis/disable', 'ctl_example_redis@disable')->name('admin.example_redis.disable');
            Route::get('example_redis/export', 'ctl_example_redis@export')->name('admin.example_redis.export');
            Route::get('api_req_log', 'ctl_api_req_log@index')->name('admin.api_req_log.index');
            Route::post('api_req_log/delete', 'ctl_api_req_log@delete')->name('admin.api_req_log.delete');
            Route::get('admin_user_oplog', 'ctl_admin_user_oplog@index')->name('admin.admin_user_oplog.index');
            Route::post('admin_user_oplog/delete', 'ctl_admin_user_oplog@delete')->name('admin.admin_user_oplog.delete');
            Route::get('admin_user_login_log', 'ctl_admin_user_login_log@index')->name('admin.admin_user_login_log.index');
            Route::post('admin_user_login_log/delete', 'ctl_admin_user_login_log@delete')->name('admin.admin_user_login_log.delete');
            Route::get('agent_oplog', 'ctl_agent_oplog@index')->name('admin.agent_oplog.index');
            Route::post('agent_oplog/delete', 'ctl_agent_oplog@delete')->name('admin.agent_oplog.delete');
            Route::get('module', 'ctl_module@index')->name('admin.module.index');
            Route::get('agent_login_log', 'ctl_agent_login_log@index')->name('admin.agent_login_log.index');
            Route::post('agent_login_log/delete', 'ctl_agent_login_log@delete')->name('admin.agent_login_log.delete');
            Route::get('user_login_log', 'ctl_user_login_log@index')->name('admin.user_login_log.index');
            Route::post('user_login_log/delete', 'ctl_user_login_log@delete')->name('admin.user_login_log.delete');
            Route::get('config', 'ctl_config@index')->name('admin.config.index');
            Route::post('config/add', 'ctl_config@add')->name('admin.config.add');
            Route::post('config/edit', 'ctl_config@edit')->name('admin.config.edit');
            Route::post('config/delete', 'ctl_config@delete')->name('admin.config.delete');
            Route::get('user', 'ctl_user@index')->name('admin.user.index');
            Route::get('user/detail', 'ctl_user@detail')->name('admin.user.detail');
            Route::post('user/enable', 'ctl_user@enable')->name('admin.user.enable');
            Route::post('user/disable', 'ctl_user@disable')->name('admin.user.disable');
            Route::get('user/login_log', 'ctl_user@login_log')->name('admin.user.login_log');
            Route::post('user/update_amount', 'ctl_user@update_amount')->name('admin.user.update_amount');
            Route::get('user_black_list', 'ctl_user@black_list')->name('admin.user.black_list');
            Route::get('agent', 'ctl_agent@index')->name('admin.agent.index');
            Route::get('agent/detail', 'ctl_agent@detail')->name('admin.agent.detail');
            Route::post('agent/add', 'ctl_agent@add')->name('admin.agent.add');
            Route::post('agent/edit', 'ctl_agent@edit')->name('admin.agent.edit');
            Route::post('agent/delete', 'ctl_agent@delete')->name('admin.agent.delete');
            Route::post('agent/enable', 'ctl_agent@enable')->name('admin.agent.enable');
            Route::post('agent/disable', 'ctl_agent@disable')->name('admin.agent.disable');
            Route::get('agent/export', 'ctl_agent@export')->name('admin.agent.export');
            Route::get('admin_user', 'ctl_admin_user@index')->name('admin.admin_user.index');
            Route::get('admin_user/detail', 'ctl_admin_user@detail')->name('admin.admin_user.detail');
            Route::post('admin_user/add', 'ctl_admin_user@add')->name('admin.admin_user.add');
            Route::post('admin_user/edit', 'ctl_admin_user@edit')->name('admin.admin_user.edit');
            Route::post('admin_user/delete', 'ctl_admin_user@delete')->name('admin.admin_user.delete');
            Route::post('admin_user/enable', 'ctl_admin_user@enable')->name('admin.admin_user.enable');
            Route::post('admin_user/disable', 'ctl_admin_user@disable')->name('admin.admin_user.disable');
            //Route::get('admin_user/export', 'ctl_admin_user@export')->name('admin.admin_user.export');
            Route::get('role', 'ctl_role@index')->name('admin.role.index');
            Route::get('role/detail', 'ctl_role@detail')->name('admin.role.detail');
            Route::post('role/add', 'ctl_role@add')->name('admin.role.add');
            Route::post('role/edit', 'ctl_role@edit')->name('admin.role.edit');
            Route::post('role/delete', 'ctl_role@delete')->name('admin.role.delete');
            Route::get('member_active_list', 'ctl_report@member_active_list')->name('admin.report.member_active_list');
            Route::get('member_active_list/export', 'ctl_report@export_member_active')->name('admin.report.export_member_active');
            Route::get('member_retention_list', 'ctl_report@member_retention_list')->name('admin.report.member_retention_list');
            Route::get('member_retention_list/export', 'ctl_report@export_member_retention')->name('admin.report.export_member_retention');
            Route::get('member_increase_list', 'ctl_report@member_increase_list')->name('admin.report.member_increase_list');
            Route::get('member_online_list', 'ctl_report@member_online_list')->name('admin.report.member_online_list');
            Route::get('member_online_list/export', 'ctl_report@export_member_online')->name('admin.report.export_member_online');
            Route::get('marquee', 'ctl_marquee@index')->name('admin.marquee.index');
            Route::get('marquee/detail', 'ctl_marquee@detail')->name('admin.marquee.detail');
            Route::post('marquee/add', 'ctl_marquee@add')->name('admin.marquee.add');
            Route::post('marquee/edit', 'ctl_marquee@edit')->name('admin.marquee.edit');
            Route::post('marquee/delete', 'ctl_marquee@delete')->name('admin.marquee.delete');
            Route::post('marquee/enable', 'ctl_marquee@enable')->name('admin.marquee.enable');
            Route::post('marquee/disable', 'ctl_marquee@disable')->name('admin.marquee.disable');
            Route::post('maintenance/edit', 'ctl_system@edit_maintenance')->name('admin.system.edit_maintenance');
            Route::get('maintenance', 'ctl_system@maintenance')->name('admin.system.maintenance');
            Route::post('config_table/edit', 'ctl_system@edit_config_table')->name('admin.system.edit_config_table');
            Route::get('config_table', 'ctl_system@config_table')->name('admin.system.config_table');
            Route::post('config_game/edit', 'ctl_system@edit_config_game')->name('admin.system.edit_config_game');
            Route::get('config_game', 'ctl_system@config_game')->name('admin.system.config_game');
            Route::get('menu', 'ctl_menu@index')->name('admin.menu.index');
            Route::post('menu/add', 'ctl_menu@add')->name('admin.menu.add');
            Route::post('menu/edit', 'ctl_menu@edit')->name('admin.menu.edit');
            Route::post('menu/delete', 'ctl_menu@delete')->name('admin.menu.delete');
            Route::get('agent_menu', 'ctl_agent_menu@index')->name('admin.agent_menu.index');
            Route::post('agent_menu/add', 'ctl_agent_menu@add')->name('admin.agent_menu.add');
            Route::post('agent_menu/edit', 'ctl_agent_menu@edit')->name('admin.agent_menu.edit');
            Route::post('agent_menu/delete', 'ctl_agent_menu@delete')->name('admin.agent_menu.delete');
            Route::get('room', 'ctl_room@index')->name('admin.room.index');
            Route::get('room/detail', 'ctl_room@detail')->name('admin.room.detail');
            Route::post('room/add', 'ctl_room@add')->name('admin.room.add');
            Route::post('room/edit', 'ctl_room@edit')->name('admin.room.edit');
            Route::post('room/delete', 'ctl_room@delete')->name('admin.room.delete');
            Route::post('room/enable', 'ctl_room@enable')->name('admin.room.enable');
            Route::post('room/disable', 'ctl_room@disable')->name('admin.room.disable');
            Route::get('app_key', 'ctl_app_key@index')->name('admin.app_key.index');
            Route::post('generate_app_key', 'ctl_app_key@generate_app_key')->name('admin.app_key.generate_app_key');
            Route::get('app_key/detail', 'ctl_app_key@detail')->name('admin.app_key.detail');
            Route::post('app_key/add', 'ctl_app_key@add')->name('admin.app_key.add');
            Route::post('app_key/edit', 'ctl_app_key@edit')->name('admin.app_key.edit');
            Route::post('app_key/delete', 'ctl_app_key@delete')->name('admin.app_key.delete');
            Route::get('wallet_log', 'ctl_wallet_log@index')->name('admin.wallet_log.index');
            Route::get('user_balance_data', 'ctl_report@user_balance_data')->name('admin.report.user_balance_data');
            Route::get('user_balance_data/export', 'ctl_report@export_user_balance')->name('admin.report.export_user_balance');
            Route::get('agent_balance_data', 'ctl_report@agent_balance_data')->name('admin.report.agent_balance_data');
            Route::get('agent_balance_data/export', 'ctl_report@export_agent_balance')->name('admin.report.export_agent_balance');
            Route::get('game_table', 'ctl_game_table@index')->name('admin.game_table.index');
            Route::get('game_table/detail', 'ctl_game_table@detail')->name('admin.game_table.detail');
            Route::post('game_table/delete', 'ctl_game_table@delete')->name('admin.game_table.delete');
            Route::get('cache/clear', 'ctl_cache@clear')->name('admin.cache.clear');
            Route::get('agent_income_list', 'ctl_agent_income@index')->name('admin.agent_income.index');
            Route::get('agent_income_statistics', 'ctl_agent_income@get_statistics')->name('admin.agent_income.get_statistics');
            Route::get('agent_income_list/export', 'ctl_agent_income@export')->name('admin.agent_income.export');
            Route::get('game_table_history', 'ctl_game_table@history')->name('admin.game_table.history');
            Route::get('game_table_history/detail', 'ctl_game_table@history_detail')->name('admin.game_table.history_detail');
            Route::get('winloss', 'ctl_winloss@index')->name('admin.winloss.index');
            Route::get('round_list', 'ctl_game_table@round_list')->name('admin.game_table.round_list');
            Route::get('order_transfer', 'ctl_order_transfer@index')->name('admin.order_transfer.index');
        });
    });
});
