<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_game_table;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_agent;
use App\repositories\repo_game_round;
use App\repositories\repo_game_table;
use App\repositories\repo_user;
use App\repositories\repo_winloss;
use App\services\serv_rpc_client;
use App\traits\trait_ctl_game_table;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ctl_game_table extends Controller
{
    use trait_ctl_game_table;

    private $repo_game_table;
    private $repo_admin_user_oplog;
    private $module_id;
    private $repo_game_round;
    private $repo_agent;
    private $repo_user;
    private $repo_winloss;
    private $serv_rpc_client;

    public function __construct(
        repo_game_table $repo_game_table,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_game_round $repo_game_round,
        repo_agent $repo_agent,
        repo_user $repo_user,
        repo_winloss $repo_winloss,
        serv_rpc_client $serv_rpc_client
    )
    {
        parent::__construct();
        $this->repo_game_table          = $repo_game_table;
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
        $this->repo_game_round          = $repo_game_round;
        $this->repo_agent               = $repo_agent;
        $this->repo_user                = $repo_user;
        $this->repo_winloss             = $repo_winloss;
        $this->serv_rpc_client          = $serv_rpc_client;
        $this->module_id = 15;
    }

    /**
     * 获取桌子列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $id         = $request->input('id', '');
        $agent_id   = $request->input('agent_id', '');
        $uid        = $request->input('uid', '');
        $room_id    = $request->input('room_id', '');
        $page_size  = $request->input('page_size', $this->repo_game_table->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //创建开始时间
        $date_end   = $request->input('date_end'); //创建结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        //桌号模糊搜
        $ids = [];
        if($id)
        {
            $game_tables = $this->repo_game_table->lists([
                'fields'     => ['id'],
                'where'     => [
                    ['id', 'like', "%{$id}%"],
                ]
            ])->toArray();
            $ids = sql_in($game_tables, 'id'); //数组
        }

        $conds = [
            'id'            => $ids,
            'agent_id'      => $agent_id,
            'uid'           => $uid,
            'room_id'       => $room_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'is_closed'     => 0, //捞未过结束时间
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['start_time_text', 'end_time_text', 'type_text',
                'create_time_text', 'duration_text'], //扩充字段
            'load'          => ['room_maps', 'user_maps', 'agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_game_table->get_list($conds);

        //获取桌子当前抽水
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => sql_in($rows['lists'], 'id'), //获取桌子id
        ]);
        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $total_platform_commission = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_platform_commission'] : '0.00';
            $total_table_owner_commission = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_table_owner_commission'] : '0.00';
            //实时展示平台抽水+桌主抽水
            $total_commission = $total_platform_commission + $total_table_owner_commission;
            $rows['lists'][$k]['commission'] = money($total_commission, '');
        }
        return res_success($rows);
    }

    /**
     * 获取牌桌历史记录列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $id         = $request->input('id', '');
        $agent_id   = $request->input('agent_id', '');
        $uid        = $request->input('uid', '');
        $room_id   = $request->input('room_id', '');
        $page_size  = $request->input('page_size', $this->repo_game_table->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //创建开始时间
        $date_end   = $request->input('date_end'); //创建结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        //桌号模糊搜
        $ids = [];
        if($id)
        {
            $game_tables = $this->repo_game_table->lists([
                'fields'     => ['id'],
                'where'     => [
                    ['id', 'like', "%{$id}%"],
                ]
            ])->toArray();
            $ids = sql_in($game_tables, 'id'); //数组
        }

        $conds = [
            'id'            => $ids,
            'agent_id'      => $agent_id,
            'uid'           => $uid,
            'room_id'       => $room_id,
            'date_start'    => $date_start, //只匹配create_time字段
            'date_end'      => $date_end, //只匹配create_time字段
            'is_closed'     => 1, //捞已过结束时间
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['start_time_text', 'end_time_text', 'type_text',
                'create_time_text', 'duration_text'], //扩充字段
            'load'          => ['room_maps', 'user_maps', 'agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_game_table->get_list($conds);

        //获取桌子当前抽水
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => sql_in($rows['lists'], 'id'), //获取桌子id
        ]);
        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $total_platform_commission = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_platform_commission'] : '0.00';
            $total_table_owner_commission = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_table_owner_commission'] : '0.00';
            //实时展示平台抽水+桌主抽水
            $total_commission = $total_platform_commission + $total_table_owner_commission;
            $rows['lists'][$k]['commission'] = money($total_commission, '');
        }
        return res_success($rows);
    }

    /**
     * 删除
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

//        $status = $this->repo_game_table->del(['id' => $id]);
//        if($status < 0)
//        {
//            return res_error($this->repo_game_table->get_err_msg($status), $status);
//        }
        //通知游戏服
        $ret = $this->serv_rpc_client->delete_table([
            'id'        => $id,
            'user_id'   => defined('AUTH_UID') ? AUTH_UID : ''
        ]);
        if(empty($ret))
        {
            return res_error('删除失败', -1);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("桌子刪除 {$id}", $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }

    /**
     * 获取牌局列表
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function round_list(Request $request)
    {
        $id         = $request->input('id', '');
        $round_id   = $request->input('round_id', '');
        $page_size  = $request->input('page_size', $this->repo_game_table->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //结算开始时间
        $date_end   = $request->input('date_end'); //结算结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            'table_id'      => $id,         //桌子id
            'round_id'      => $round_id,   //牌局id
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['settle_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_game_round->get_list($conds);
        return res_success($rows);
    }
}
