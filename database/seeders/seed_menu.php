<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'parent_id',
            'level',
            'name',
            'type',
            'guard_name',
            'url',
            'icon',
            'perms',
            'sort',
            'is_show',
            'status',
        ];

        $rows = [
            [0, 0, '用户数据查询', 1, 'admin', '/users/all_users', 'peoples', '', 0, 1, 1],
            [0, 0, '渠道', 1, 'admin', '/channel/channel_list', 'tab', '', 1, 1, 1],
            [0, 0, '数据统计', 1, 'admin', '/statistics/user_online', 'chart', '', 2, 1, 1],
            [0, 0, '限红', 1, 'admin', '/redlimit/limit_red_record', 'excel', '', 3, 1, 1],
            [0, 0, '游戏配置', 1, 'admin', '/gameconfiguration/live_room', 'tree', '', 4, 1, 1],
            [0, 0, '游戏记录', 1, 'admin', '/gamerecord/income_record', 'table', '', 5, 1, 1],
            [0, 0, '后台管理', 1, 'admin', '/accountsetting/role', 'password', '', 6, 1, 1],
            [0, 0, '用户数据查询', 1, 'agent', '/users/all_users', 'peoples', '', 0, 1, 1], //代理一级菜单
            [0, 0, '数据统计', 1, 'agent', '/statistics/user_online', 'chart', '', 2, 1, 1],
            [0, 0, '限红', 1, 'agent', '/redlimit/limit_red_record', 'excel', '', 3, 1, 1],
            [0, 0, '游戏配置', 1, 'agent', '/gameconfiguration/table_record', 'tree', '', 4, 1, 1],
            [0, 0, '游戏记录', 1, 'agent', '/gamerecord/income_record', 'table', '', 5, 1, 1],
            [0, 0, '后台管理', 1, 'agent', '/accountsetting/role', 'password', '', 6, 1, 1],
            [1, 1, '所有用户', 1, 'admin', '/users/all_users', '', 'admin.user.index', 0, 1, 1],
            [1, 1, '封禁用户', 1, 'admin', '', '', 'admin.user.disable', 0, 0, 1],
            [1, 1, '解封用户', 1, 'admin', '', '', 'admin.user.enable', 0, 0, 1],
            [1, 1, '登陆记录', 1, 'admin', '/users/login_record', '', 'admin.user.login_log', 0, 0, 1],
            [1, 1, '黑名单', 1, 'admin', '/users/blacklist', '', 'admin.user.black_list', 1, 1, 1],
            [1, 1, '修改额度', 1, 'admin', '', '', 'admin.user.update_amount', 0, 0, 1],
            [1, 1, '余额修改记录', 1, 'admin', '/users/balance_modification_record', '', 'admin.order_transfer.index', 2, 1, 1],
            [2, 1, '渠道列表', 1, 'admin', '/channel/channel_list', '', 'admin.agent.index', 0, 1, 1],
            [2, 1, '新增渠道', 1, 'admin', '', '', 'admin.agent.add', 0, 0, 1],
            [2, 1, '编辑渠道', 1, 'admin', '', '', 'admin.agent.edit', 0, 0, 1],
            [2, 1, '开启渠道', 1, 'admin', '', '', 'admin.agent.enable', 0, 0, 1],
            [2, 1, '关闭渠道', 1, 'admin', '', '', 'admin.agent.disable', 0, 0, 1],
            [3, 1, '用户在线', 1, 'admin', '/statistics/user_online', '', 'admin.report.member_online_list', 0, 1, 1],
            [3, 1, '用户在线导出', 1, 'admin', '', '', 'admin.report.export_member_online', 0, 0, 1],
            [3, 1, '用户活跃', 1, 'admin', '/statistics/user_active', '', 'admin.report.member_active_list', 1, 1, 1],
            [3, 1, '用户留存', 1, 'admin', '/statistics/user_retention', '', 'admin.report.member_retention_list', 2, 1, 1],
            [3, 1, '新增用户', 1, 'admin', '/statistics/user_growth', '', 'admin.report.member_increase_list', 3, 1, 1],
            [4, 1, '渠道取款记录', 1, 'admin', '/redlimit/limit_red_record', '', 'admin.report.agent_balance_data', 0, 1, 1],
            [4, 1, '用户取款记录', 1, 'admin', '/redlimit/user_redemption', '', 'admin.report.user_balance_data', 1, 1, 1],
            [5, 1, '房间列表', 1, 'admin', '/gameconfiguration/live_room', '', 'admin.room.index', 0, 1, 1],
            [5, 1, '房间详情', 1, 'admin', '', '', 'admin.room.detail', 0, 0, 1],
            [5, 1, '房间添加', 1, 'admin', '', '', 'admin.room.add', 0, 0, 1],
            [5, 1, '房间编辑', 1, 'admin', '', '', 'admin.room.edit', 0, 0, 1],
            [5, 1, '房间删除', 1, 'admin', '', '', 'admin.room.delete', 0, 0, 1],
            [5, 1, '房间启用', 1, 'admin', '', '', 'admin.room.enable', 0, 0, 1],
            [5, 1, '房间禁用', 1, 'admin', '', '', 'admin.room.disable', 0, 0, 1],
            [5, 1, '桌子列表', 1, 'admin', '/gameconfiguration/table_record', '', 'admin.game_table.index', 1, 1, 1],
            [5, 1, '桌子详情', 1, 'admin', '/game_record_detai', '', 'admin.game_table.detail', 0, 0, 1],
            [5, 1, '牌局列表', 1, 'admin', '', '', 'admin.game_table.round_list', 0, 0, 1],
            [5, 1, '桌子删除', 1, 'admin', '', '', 'admin.game_table.delete', 0, 0, 1],
            [5, 1, '建桌配置', 1, 'admin', '/gameconfiguration/table_configuration', '', 'admin.system.config_table', 2, 1, 1],
            [5, 1, '建桌配置保存', 1, 'admin', '', '', 'admin.system.edit_config_table', 0, 0, 1],
            [5, 1, '游戏基本配置', 1, 'admin', '/gameconfiguration/game_configuration', '', 'admin.system.config_game', 3, 1, 1],
            [5, 1, '游戏基本配置保存', 1, 'admin', '', '', 'admin.system.edit_config_game', 0, 0, 1],
            [5, 1, '维护', 1, 'admin', '/gameconfiguration/maintenance', '', 'admin.system.maintenance', 4, 1, 1],
            [5, 1, '维护通知设置', 1, 'admin', '', '', 'admin.system.edit_maintenance', 0, 0, 1],
            [5, 1, '跑马灯', 1, 'admin', '/gameconfiguration/marquee', '', 'admin.marquee.index', 5, 1, 1],
            [5, 1, '跑马灯添加', 1, 'admin', '', '', 'admin.marquee.add', 0, 0, 1],
            [5, 1, '跑马灯编辑', 1, 'admin', '', '', 'admin.marquee.edit', 0, 0, 1],
            [5, 1, '跑马灯删除', 1, 'admin', '', '', 'admin.marquee.delete', 0, 0, 1],
            [5, 1, '跑马灯启用', 1, 'admin', '', '', 'admin.marquee.enable', 0, 0, 1],
            [5, 1, '跑马灯禁用', 1, 'admin', '', '', 'admin.marquee.disable', 0, 0, 1],
            [6, 1, '收入记录', 1, 'admin', '/gamerecord/income_record', '', 'admin.agent_income.index', 0, 1, 1],
            [6, 1, '收入统计信息', 1, 'admin', '', '', 'admin.agent_income.get_statistics', 0, 0, 1],
            [6, 1, '收入记录导出', 1, 'admin', '', '', 'admin.agent_income.export', 0, 0, 1],
            [6, 1, '牌桌记录', 1, 'admin', '/gamerecord', '', 'admin.game_table.history', 1, 1, 1],
            [6, 1, '牌桌记录详情', 1, 'admin', '/game_record_detai', '', 'admin.game_table.history_detail', 0, 0, 1],
            [6, 1, '用户查询', 1, 'admin', '/gamerecord/user_query', '', 'admin.winloss.index', 2, 1, 1],
            [7, 1, '账号列表', 1, 'admin', '/accountsetting/role', '', 'admin.admin_user.index', 0, 1, 1],
            [7, 1, '账号创建', 1, 'admin', '', '', 'admin.admin_user.add', 0, 0, 1],
            [7, 1, '账号编辑', 1, 'admin', '', '', 'admin.admin_user.edit', 0, 0, 1],
            [7, 1, '账号删除', 1, 'admin', '', '', 'admin.admin_user.delete', 0, 0, 1],
            [7, 1, '账号启用', 1, 'admin', '', '', 'admin.admin_user.enable', 0, 0, 1],
            [7, 1, '账号禁用', 1, 'admin', '', '', 'admin.admin_user.disable', 0, 0, 1],
            [7, 1, '角色列表', 1, 'admin', '/accountsetting/permission', '', 'admin.role.index', 1, 1, 1],
            [7, 1, '创建角色', 1, 'admin', '', '', 'admin.role.add', 0, 0, 1],
            [7, 1, '编辑角色', 1, 'admin', '', '', 'admin.role.edit', 0, 0, 1],
            [7, 1, '删除角色', 1, 'admin', '', '', 'admin.role.delete', 0, 0, 1],
            [7, 1, '角色详情', 1, 'admin', '', '', 'admin.role.detail', 0, 0, 1],
            [7, 1, '管理操作日志', 1, 'admin', '/accountsetting/operation_log', '', 'admin.admin_user_oplog.index', 2, 1, 1],
            [7, 1, '代理操作日志', 1, 'admin', '/accountsetting/agent_log', '', 'admin.agent_oplog.index', 3, 1, 1],

            [8, 1, '所有用户', 1, 'agent', '/users/all_users', '', 'adminag.user.index', 0, 1, 1], //代理二级菜单
            [8, 1, '封禁用户', 1, 'agent', '', '', 'adminag.user.disable', 0, 0, 1],
            [8, 1, '解封用户', 1, 'agent', '', '', 'adminag.user.enable', 0, 0, 1],
            [8, 1, '登陆记录', 1, 'agent', '/users/login_record', '', 'adminag.user.login_log', 0, 0, 1],
            [8, 1, '黑名单', 1, 'agent', '/users/blacklist', '', 'adminag.user.black_list', 1, 1, 1],
            [9, 1, '用户在线', 1, 'agent', '/statistics/user_online', '', 'adminag.report.member_online_list', 0, 1, 1],
            [9, 1, '用户在线导出', 1, 'agent', '', '', 'adminag.report.export_member_online', 0, 0, 1],
            [9, 1, '用户活跃', 1, 'agent', '/statistics/user_active', '', 'adminag.report.member_active_list', 1, 1, 1],
            [9, 1, '用户留存', 1, 'agent', '/statistics/user_retention', '', 'adminag.report.member_retention_list', 2, 1, 1],
            [9, 1, '新增用户', 1, 'agent', '/statistics/user_growth', '', 'adminag.report.member_increase_list', 3, 1, 1],
            [10, 1, '渠道取款记录', 1, 'agent', '/redlimit/limit_red_record', '', 'adminag.report.agent_balance_data', 0, 1, 1],
            [10, 1, '用户取款记录', 1, 'agent', '/redlimit/user_redemption', '', 'adminag.report.user_balance_data', 1, 1, 1],
            [11, 1, '桌子列表', 1, 'agent', '/gameconfiguration/table_record', '', 'adminag.game_table.index', 1, 1, 1],
            [11, 1, '桌子详情', 1, 'agent', '/game_record_detai', '', 'adminag.game_table.detail', 0, 0, 1],
            [11, 1, '牌局列表', 1, 'agent', '', '', 'adminag.game_table.round_list', 0, 0, 1],
            [11, 1, '桌子删除', 1, 'agent', '', '', 'adminag.game_table.delete', 0, 0, 1],
            [12, 1, '收入记录', 1, 'agent', '/gamerecord/income_record', '', 'adminag.agent_income.index', 0, 1, 1],
            [12, 1, '收入统计信息', 1, 'agent', '', '', 'adminag.agent_income.get_statistics', 0, 0, 1],
            [12, 1, '收入记录导出', 1, 'agent', '', '', 'adminag.agent_income.export', 0, 0, 1],
            [12, 1, '牌桌记录', 1, 'agent', '/gamerecord', '', 'adminag.game_table.history', 1, 1, 1],
            [12, 1, '牌桌记录详情', 1, 'agent', '/game_record_detai', '', 'adminag.game_table.history_detail', 0, 0, 1],
            [12, 1, '用户查询', 1, 'agent', '/gamerecord/user_query', '', 'adminag.winloss.index', 2, 1, 1],
            [13, 1, '子账号列表', 1, 'agent', '/accountsetting/permission', '', 'adminag.agent.index', 0, 1, 1],
            [13, 1, '子账号详情', 1, 'agent', '', '', 'adminag.agent.detail', 0, 0, 1],
            [13, 1, '子账号创建', 1, 'agent', '', '', 'adminag.agent.add', 0, 0, 1],
            [13, 1, '子账号编辑', 1, 'agent', '', '', 'adminag.agent.edit', 0, 0, 1],
            [13, 1, '子账号删除', 1, 'agent', '', '', 'adminag.agent.delete', 0, 0, 1],
            [13, 1, '子账号启用', 1, 'agent', '', '', 'adminag.agent.enable', 0, 0, 1],
            [13, 1, '子账号禁用', 1, 'agent', '', '', 'adminag.agent.disable', 0, 0, 1],
        ];

        $insert_data = [];
        foreach ($rows as $row)
        {
            $item = [];
            foreach ($fields as $k => $field)
            {
                $item[$field] = $row[$k];
            }
            $insert_data[] = $item;
        }
        DB::table('menu')->insert($insert_data);
    }
}
