<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_config extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'type',
            'name',
            'value',
            'title',
            'group',
            'sort',
        ];

        $rows = [
            ['string', 'site_name', 'Laravel开发框架', '主站名称', 'config', 0],
            ['string', 'site_description', 'Laravel开发框架', '主站摘要信息', 'config', 0],
            ['string', 'site_keyword', 'Laravel开发框架', '主站关键字', 'config', 0],
            ['string', 'maintenance_title', '', '维护通知标题', 'config', 0],
            ['string', 'maintenance_content', '', '维护通知内容', 'config', 0],
            ['int', 'sys_in_maintenance', 0, '系统是否维护中', 'config', 0],
            ['float', 'min_order', 100.00, '建桌低消', 'config_table', 0],
            ['string', 'single_bet_limit', '100,200,300,400', '建桌单注', 'config_table', 0],
            ['float', 'banker_pay_limit', 10000.00, '系统公庄1门赔付上限', 'config_game', 0],
            ['string', 'build_time', '1,2,3,4,5,6,7,8,9,10', '建桌时长', 'config_table', 0],
            ['int', 'platform_commission', 3, '建桌系统抽水%', 'config_table', 0],
            ['int', 'table_owner_commission', 2, '建桌桌主抽水%', 'config_table', 0],
            ['int', 'is_open_credit_table', 0, '是否开启信用桌', 'config_table', 0],
            ['int', 'credit_table_bring_in_min', 1, '信用桌带入下限', 'config_table', 0],
            ['int', 'credit_table_bring_in_max', 10, '信用桌带入上限', 'config_table', 0],
            ['string', 'bet_times', '1,2,3,4', '游戏桌下注倍数', 'config_game', 0],
            ['string', 'card_head', '1,2,3,4,5,6', '牌头选项', 'config_game', 0],
            ['int', 'bet_countdown_time', 10, '下注倒计时', 'config_game', 0],
            ['int', 'card_head_time', 10, '选择牌头时间', 'config_game', 0],
            ['int', 'settle_time', 10, '结算时间', 'config_game', 0],
            ['int', 'open_card_time', 10, '开牌时间', 'config_game', 0],
            ['int', 'sz_min_mpl', 1, '上庄最小倍数', 'config_game', 0],
            ['int', 'gz_min_mpl', 1, '公庄最小倍数', 'config_game', 0],
            ['int', 'bz_min_mpl', 1, '帮庄最小倍数', 'config_game', 0],
            ['string', 'auto_bet_time', '1,3,6,12,24,-1', '自动投注时间', 'config_game', 0],
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
        DB::table('config')->insert($insert_data); //走批量插入
    }
}
