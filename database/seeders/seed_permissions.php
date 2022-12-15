<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_permissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $created_at = date('Y-m-d H:i:s');

        $fields = [
            'name',
            'guard_name',
            'display_name',
            'module_id',
            'created_at',
        ];

        $rows = [
            ['admin.user.index', 'admin', '用户列表', 1, $created_at],
            ['admin.user.login_log', 'admin', '登陆记录', 1, $created_at],
            ['admin.user.enable', 'admin', '解封用户', 1, $created_at],
            ['admin.user.disable', 'admin', '封禁用户', 1, $created_at],
            ['admin.user.black_list', 'admin', '黑名单', 1, $created_at],
            ['admin.user.update_amount', 'admin', '修改额度', 1, $created_at],
            ['admin.order_transfer.index', 'admin', '余额修改记录', 1, $created_at],
            ['admin.agent.index', 'admin', '渠道列表', 2, $created_at],
            ['admin.agent.add', 'admin', '新增渠道', 2, $created_at],
            ['admin.agent.edit', 'admin', '编辑渠道', 2, $created_at],
            ['admin.agent.enable', 'admin', '开启渠道', 2, $created_at],
            ['admin.agent.disable', 'admin', '关闭渠道', 2, $created_at],
            ['admin.report.member_online_list', 'admin', '用户在线列表', 3, $created_at],
            ['admin.report.export_member_online', 'admin', '用户在线导出', 3, $created_at],
            ['admin.report.member_active_list', 'admin', '用户活跃列表', 4, $created_at],
            ['admin.report.member_retention_list', 'admin', '用户留存列表', 5, $created_at],
            ['admin.report.member_increase_list', 'admin', '新增用户列表', 28, $created_at],
            ['admin.report.agent_balance_data', 'admin', '渠道取款记录', 6, $created_at],
            ['admin.report.user_balance_data', 'admin', '用户取款记录', 7, $created_at],
            ['admin.room.index', 'admin', '房间列表', 8, $created_at],
            ['admin.room.detail', 'admin', '房间详情', 8, $created_at],
            ['admin.room.add', 'admin', '房间添加', 8, $created_at],
            ['admin.room.edit', 'admin', '房间编辑', 8, $created_at],
            //['admin.room.delete', 'admin', '房间删除', 8, $created_at],
            ['admin.room.enable', 'admin', '房间启用', 8, $created_at],
            ['admin.room.disable', 'admin', '房间禁用', 8, $created_at],
            ['admin.game_table.index', 'admin', '桌子列表', 9, $created_at],
            ['admin.game_table.detail', 'admin', '桌子详情', 9, $created_at],
            ['admin.game_table.round_list', 'admin', '牌局列表', 9, $created_at],
            ['admin.game_table.delete', 'admin', '桌子删除', 9, $created_at],
            ['admin.system.config_table', 'admin', '获取建桌配置', 10, $created_at],
            ['admin.system.edit_config_table', 'admin', '建桌配置', 10, $created_at],
            ['admin.system.config_game', 'admin', '获取游戏基本配置', 11, $created_at],
            ['admin.system.edit_config_game', 'admin', '游戏基本配置', 11, $created_at],
            ['admin.system.maintenance', 'admin', '获取维护通知', 12, $created_at],
            ['admin.system.edit_maintenance', 'admin', '维护通知设置', 12, $created_at],
            ['admin.marquee.index', 'admin', '跑马灯列表', 13, $created_at],
            ['admin.marquee.add', 'admin', '跑马灯添加', 13, $created_at],
            ['admin.marquee.edit', 'admin', '跑马灯编辑', 13, $created_at],
            ['admin.marquee.delete', 'admin', '跑马灯删除', 13, $created_at],
            ['admin.marquee.enable', 'admin', '跑马灯启用', 13, $created_at],
            ['admin.marquee.disable', 'admin', '跑马灯禁用', 13, $created_at],
            ['admin.agent_income.index', 'admin', '渠道收入记录', 14, $created_at],
            ['admin.agent_income.get_statistics', 'admin', '渠道收入统计信息', 14, $created_at],
            ['admin.agent_income.export', 'admin', '渠道收入记录导出', 14, $created_at],
            ['admin.game_table.history', 'admin', '牌桌记录', 15, $created_at],
            ['admin.game_table.history_detail', 'admin', '牌桌记录详情', 15, $created_at],
            ['admin.winloss.index', 'admin', '用户查询', 16, $created_at],
            ['admin.role.index', 'admin', '角色列表', 17, $created_at],
            ['admin.role.detail', 'admin', '角色详情', 17, $created_at],
            ['admin.role.add', 'admin', '创建角色', 17, $created_at],
            ['admin.role.edit', 'admin', '编辑角色', 17, $created_at],
            ['admin.role.delete', 'admin', '删除角色', 17, $created_at],
            ['admin.admin_user.index', 'admin', '管理员列表', 18, $created_at],
            ['admin.admin_user.add', 'admin', '管理员创建', 18, $created_at],
            ['admin.admin_user.edit', 'admin', '管理员编辑', 18, $created_at],
            ['admin.admin_user.delete', 'admin', '管理员删除', 18, $created_at],
            ['admin.admin_user.enable', 'admin', '管理员启用', 18, $created_at],
            ['admin.admin_user.disable', 'admin', '管理员禁用', 18, $created_at],
            ['admin.admin_user_oplog.index', 'admin', '管理操作日志列表', 19, $created_at],
            ['admin.agent_oplog.index', 'admin', '代理操作日志列表', 20, $created_at],

            ['adminag.user.index', 'agent', '用户列表', 1, $created_at], //代理权限
            ['adminag.user.login_log', 'agent', '登陆记录', 1, $created_at],
            ['adminag.user.enable', 'agent', '解封用户', 1, $created_at],
            ['adminag.user.disable', 'agent', '封禁用户', 1, $created_at],
            ['adminag.user.black_list', 'agent', '黑名单', 1, $created_at],
            ['adminag.report.member_online_list', 'agent', '用户在线列表', 3, $created_at],
            ['adminag.report.export_member_online', 'agent', '用户在线导出', 3, $created_at],
            ['adminag.report.member_active_list', 'agent', '用户活跃列表', 4, $created_at],
            ['adminag.report.member_retention_list', 'agent', '用户留存列表', 5, $created_at],
            ['adminag.report.member_increase_list', 'agent', '新增用户列表', 28, $created_at],
            ['adminag.report.agent_balance_data', 'agent', '渠道取款记录', 6, $created_at],
            ['adminag.report.user_balance_data', 'agent', '用户取款记录', 7, $created_at],
            ['adminag.game_table.index', 'agent', '桌子列表', 9, $created_at],
            ['adminag.game_table.detail', 'agent', '桌子详情', 9, $created_at],
            ['adminag.game_table.round_list', 'agent', '牌局列表', 9, $created_at],
            ['adminag.game_table.delete', 'agent', '桌子删除', 9, $created_at],
            ['adminag.agent_income.index', 'agent', '渠道收入记录', 14, $created_at],
            ['adminag.agent_income.get_statistics', 'agent', '渠道收入统计信息', 14, $created_at],
            ['adminag.agent_income.export', 'agent', '渠道收入记录导出', 14, $created_at],
            ['adminag.game_table.history', 'agent', '牌桌记录', 15, $created_at],
            ['adminag.game_table.history_detail', 'agent', '牌桌记录详情', 15, $created_at],
            ['adminag.winloss.index', 'agent', '用户查询', 16, $created_at],
            ['adminag.role.index', 'agent', '角色列表', 17, $created_at],
            ['adminag.role.add', 'agent', '创建角色', 17, $created_at],
            ['adminag.role.edit', 'agent', '编辑角色', 17, $created_at],
            ['adminag.role.delete', 'agent', '删除角色', 17, $created_at],
            ['adminag.agent.index', 'agent', '子帐号列表', 22, $created_at],
            ['adminag.agent.detail', 'agent', '子帐号详情', 22, $created_at],
            ['adminag.agent.add', 'agent', '新增子帐号', 22, $created_at],
            ['adminag.agent.edit', 'agent', '编辑子帐号', 22, $created_at],
            ['adminag.agent.delete', 'agent', '删除子帐号', 22, $created_at],
            ['adminag.agent.enable', 'agent', '开启子帐号', 22, $created_at],
            ['adminag.agent.disable', 'agent', '关闭子帐号', 22, $created_at],
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
        DB::table('permissions')->insert($insert_data);
    }
}
