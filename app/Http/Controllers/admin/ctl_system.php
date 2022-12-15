<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_config;
use App\services\serv_rpc_client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * 系统配置
 * Class ctl_system
 * @package App\Http\Controllers\admin
 */
class ctl_system extends Controller
{
    private $repo_config;
    private $repo_admin_user_oplog;
    private $module_maintenance_id;
    private $module_table_id;
    private $module_game_id;
    private $serv_rpc_client;

    public function __construct(
        repo_config $repo_config,
        repo_admin_user_oplog $repo_admin_user_oplog,
        serv_rpc_client $serv_rpc_client
    )
    {
        parent::__construct();
        $this->repo_config          = $repo_config;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->serv_rpc_client      = $serv_rpc_client;
        $this->module_maintenance_id = 12;
        $this->module_table_id = 10;
        $this->module_game_id = 11;
    }

    /**
     * 系统维护配置
     * @param Request $request
     * @return mixed
     */
    public function edit_maintenance(Request $request)
    {
        //系统是否维护中
        $sys_in_maintenance = $request->input('sys_in_maintenance', 0);
        $config_fields = [
            'maintenance_title'     => 'config', //config为分组
            'maintenance_content'   => 'config',
            'sys_in_maintenance'    => 'config',
        ];

        $data = [];
        foreach ($config_fields as $k => $v)
        {
            $data[] = [
                'name'          => $k,
                'value'         => $request->input($k, ''),
                'group'         => $v,
                'update_time'   => time(),
                'update_user'   => defined('AUTH_UID') ? AUTH_UID : '',
            ];
        }
        //批量更新
        $status = $this->repo_config->insertOrUpdate($data,
            ['name'],
            ['value', 'group', 'update_time', 'update_user']
        );
        if($status < 0)
        {
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //通知游戏服
        $this->serv_rpc_client->change_maintain_config([
            'title'     => $request->input('maintenance_title', ''),
            'content'   => $request->input('maintenance_content', ''),
            'mode'      => $sys_in_maintenance
        ]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("维护配置 ", $this->module_maintenance_id);
        //调用维护中命令
        $sys_in_maintenance == 1 ? Artisan::call('down') : Artisan::call('up');

        return res_success([], trans('api.api_update_success'));
    }

    /**
     * 获取系统维护配置
     * @param Request $request
     * @return mixed
     */
    public function maintenance(Request $request)
    {
        //系统是否维护中
        $sys_in_maintenance = $this->repo_config->get('sys_in_maintenance', [
            'type' => 'int', 'default' => 0, 'group' => 'config'
        ]);
        //维护通知标题
        $maintenance_title = $this->repo_config->get('maintenance_title', [
            'type' => 'string', 'default' => '', 'group' => 'config'
        ]);
        //维护通知内容
        $maintenance_content = $this->repo_config->get('maintenance_content', [
            'type' => 'text', 'default' => '', 'group' => 'config'
        ]);
        $maintenance_content = htmlspecialchars_decode($maintenance_content);

        return res_success([
            'sys_in_maintenance'    => $sys_in_maintenance,
            'maintenance_title'     => $maintenance_title,
            'maintenance_content'    => $maintenance_content,
        ]);
    }

    /**
     * 建桌配置
     * @param Request $request
     * @return mixed
     */
    public function edit_config_table(Request $request)
    {
        $config_fields = [
            'min_order'                 => 'config_table', //config_table为分组
            'single_bet_limit'          => 'config_table',
            'build_time'                => 'config_table',
            'platform_commission'       => 'config_table',
            'table_owner_commission'    => 'config_table',
            'is_open_credit_table'      => 'config_table',
            'credit_table_bring_in_min' => 'config_table',
            'credit_table_bring_in_max' => 'config_table',
        ];

        $data = [];
        foreach ($config_fields as $k => $v)
        {
            if($request->has($k)) //与传入参数做匹配
            {
                $data[] = [
                    'name'          => $k,
                    'value'         => $request->input($k, ''),
                    'group'         => $v,
                    'update_time'   => time(),
                    'update_user'   => defined('AUTH_UID') ? AUTH_UID : '',
                ];
            }
        }

        //批量更新
        $status = $this->repo_config->insertOrUpdate($data,
            ['name'],
            ['value', 'group', 'update_time', 'update_user']
        );
        if($status < 0)
        {
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //通知游戏服
        $this->serv_rpc_client->change_config();
        //寫入日志
        $this->repo_admin_user_oplog->add_log("建桌配置 ", $this->module_table_id);

        return res_success([], trans('api.api_update_success'));
    }

    /**
     * 获取建桌配置
     * @param Request $request
     * @return mixed
     */
    public function config_table(Request $request)
    {
        //建桌低消
        $min_order = $this->repo_config->get('min_order', ['type' => 'float', 'default' => 0.00, 'group' => 'config_table']);
        //建桌单注
        $single_bet_limit = $this->repo_config->get('single_bet_limit', ['type' => 'string', 'default' => '', 'group' => 'config_table']);
        //建桌时长
        $build_time = $this->repo_config->get('build_time', ['type' => 'string', 'default' => '', 'group' => 'config_table']);
        //建桌系统抽水%
        $platform_commission = $this->repo_config->get('platform_commission', ['type' => 'int', 'default' => 0, 'group' => 'config_table']);
        //建桌桌主抽水%
        $table_owner_commission = $this->repo_config->get('table_owner_commission', ['type' => 'int', 'default' => 0, 'group' => 'config_table']);
        //是否开启信用桌 0=否 1=是
        $is_open_credit_table = $this->repo_config->get('is_open_credit_table', ['type' => 'int', 'default' => 0, 'group' => 'config_table']);
        //信用桌带入下限
        $credit_table_bring_in_min = $this->repo_config->get('credit_table_bring_in_min', ['type' => 'int', 'default' => 0, 'group' => 'config_table']);
        //信用桌带入上限
        $credit_table_bring_in_max = $this->repo_config->get('credit_table_bring_in_max', ['type' => 'int', 'default' => 0, 'group' => 'config_table']);

        return res_success([
            'min_order'                 => $min_order,
            'single_bet_limit'          => $single_bet_limit,
            'build_time'                => $build_time,
            'platform_commission'       => $platform_commission,
            'table_owner_commission'    => $table_owner_commission,
            'is_open_credit_table'      => $is_open_credit_table,
            'credit_table_bring_in_min' => $credit_table_bring_in_min,
            'credit_table_bring_in_max' => $credit_table_bring_in_max,
        ]);
    }

    /**
     * 游戏基本配置
     * @param Request $request
     * @return mixed
     */
    public function edit_config_game(Request $request)
    {
        $config_fields = [
            'bet_times'             => 'config_game', //config_game为分组
            'card_head'             => 'config_game',
            'bet_countdown_time'    => 'config_game',
            'card_head_time'        => 'config_game',
            'settle_time'           => 'config_game', //结算时间
            'open_card_time'        => 'config_game',
            'sz_min_mpl'            => 'config_game',
            'gz_min_mpl'            => 'config_game',
            'bz_min_mpl'            => 'config_game',
            'auto_bet_time'         => 'config_game',
            'banker_pay_limit'      => 'config_game', //系统公庄赔付上限
        ];

        $data = [];
        foreach ($config_fields as $k => $v)
        {
            if($request->has($k) && !empty($request->input($k))) //与传入参数做匹配
            {
                $data[] = [
                    'name'          => $k,
                    'value'         => $request->input($k, ''),
                    'group'         => $v,
                    'update_time'   => time(),
                    'update_user'   => defined('AUTH_UID') ? AUTH_UID : '',
                ];
            }
        }
        //批量更新
        $status = $this->repo_config->insertOrUpdate($data,
            ['name'],
            ['value', 'group', 'update_time', 'update_user']
        );
        if($status < 0)
        {
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //通知游戏服
        $this->serv_rpc_client->change_config();
        //寫入日志
        $this->repo_admin_user_oplog->add_log("游戏基本配置 ", $this->module_game_id);

        return res_success([], trans('api.api_update_success'));
    }

    /**
     * 获取游戏基本配置
     * @param Request $request
     * @return mixed
     */
    public function config_game(Request $request)
    {
        //游戏桌下注倍数
        $bet_times = $this->repo_config->get('bet_times', ['type' => 'string', 'default' => '', 'group' => 'config_game']);
        //牌头选项
        $card_head = $this->repo_config->get('card_head', ['type' => 'string', 'default' => '', 'group' => 'config_game']);
        //下注倒计时
        $bet_countdown_time = $this->repo_config->get('bet_countdown_time', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //选择牌头时间
        $card_head_time = $this->repo_config->get('card_head_time', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //结算时间
        $settle_time = $this->repo_config->get('settle_time', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //开牌时间
        $open_card_time = $this->repo_config->get('open_card_time', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //上庄最小倍数
        $sz_min_mpl = $this->repo_config->get('sz_min_mpl', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //公庄最小倍
        $gz_min_mpl = $this->repo_config->get('gz_min_mpl', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //帮庄最小倍
        $bz_min_mpl = $this->repo_config->get('bz_min_mpl', ['type' => 'int', 'default' => 0, 'group' => 'config_game']);
        //自动投注时间 单位小时
        $auto_bet_time = $this->repo_config->get('auto_bet_time', ['type' => 'string', 'default' => '', 'group' => 'config_game']);
        //系统公庄赔付上限
        $banker_pay_limit = $this->repo_config->get('banker_pay_limit', ['type' => 'float', 'default' => 0.00, 'group' => 'config_game']);

        return res_success([
            'bet_times'                 => $bet_times,
            'card_head'                 => $card_head,
            'bet_countdown_time'        => $bet_countdown_time,
            'card_head_time'            => $card_head_time,
            'settle_time'               => $settle_time,
            'open_card_time'            => $open_card_time,
            'sz_min_mpl'                => $sz_min_mpl,
            'gz_min_mpl'                => $gz_min_mpl,
            'bz_min_mpl'                => $bz_min_mpl,
            'auto_bet_time'             => $auto_bet_time,
            'banker_pay_limit'          => $banker_pay_limit,
        ]);
    }
}
