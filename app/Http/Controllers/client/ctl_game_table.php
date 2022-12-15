<?php

namespace App\Http\Controllers\client;

use App\repositories\repo_game_table;
use App\repositories\repo_winloss;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_game_table extends Controller
{
    private $repo_game_table;
    private $repo_winloss;
    private $page_size;

    public function __construct(
        repo_game_table $repo_game_table,
        repo_winloss $repo_winloss
    )
    {
        parent::__construct();
        $this->repo_game_table          = $repo_game_table;
        $this->repo_winloss             = $repo_winloss;
        $this->page_size                = 10;
    }

    /**
     * 获取玩家建桌记录列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        //$uid        = $request->input('uid', '');
        $uid = defined('AUTH_UID') ? AUTH_UID : '';
        //$page_size  = $request->input('page_size', $this->repo_game_table->page_size);
        $page       = $request->input('page', 1);

        if(empty($uid))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //应包括已删除桌子
        $conds = [
            'uid'           => $uid, //桌主id
            'is_closed'     => 1, //捞已过结束时间
            'page_size'     => $this->page_size, //每页几条, 先写死防止被送1000条以上
            'page'          => $page, //第几页
            'append'        => ['start_time_text', 'end_time_text', 'type_text',
                'create_time_text', 'duration_text'], //扩充字段
            'load'          => [],
            //'count'         => 1, //是否返回总条数
            'next_page'     => 1, //是否有下一页
        ];
        $rows = $this->repo_game_table->get_list($conds);

        //获取桌子总输赢
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => sql_in($rows['lists'], 'id'), //获取桌子id
        ]);
        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $rows['lists'][$k]['member_count'] = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['member_count'] : 0;
            $rows['lists'][$k]['total_winloss_amount'] = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_winloss_amount'] : '0.00';
            $rows['lists'][$k]['total_table_owner_commission'] = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_table_owner_commission'] : '0.00'; //只展示桌主抽水
        }

        //获取该桌主所有桌号, 应包括已删除桌子
        $conds = [
            'uid'           => $uid, //桌主id
            'is_closed'     => 1, //捞已过结束时间
            'fields'        => ['id'],
        ];
        $table_rows = $this->repo_game_table->get_list($conds);
        //获取所有桌子总输赢
        $total_winloss_amount = 0;
        $total_table_owner_commission = 0;
        $all_table_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => sql_in($table_rows, 'id'), //获取桌子id
        ]);
        foreach($all_table_winloss as $v) //格式化数据
        {
            $total_winloss_amount += $v['total_winloss_amount'];
            $total_table_owner_commission += $v['total_table_owner_commission'];
        }
        //所有桌总输赢
        $rows['total_winloss_amount'] = money($total_winloss_amount, '');
        //所有桌桌主抽水
        $rows['total_table_owner_commission'] = money($total_table_owner_commission, '');

        return res_success($rows);
    }

    /**
     * 获取玩家牌桌记录
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        //$uid        = $request->input('uid', ''); //玩家id
        $uid        = defined('AUTH_UID') ? AUTH_UID : '';
        $type       = $request->input('type', ''); //类型：1=现金 2=信用
        //$page_size  = $request->input('page_size', $this->repo_game_table->page_size);
        $page       = $request->input('page', 1);

        if(empty($uid) || empty($type))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //获取该玩家下注过桌子id, 包含现金桌与信用桌, 已删除桌子
        $table_ids = $this->repo_winloss->get_table_ids_by_uid($uid);

        $conds = [
            'id'            => $table_ids,
            'type'          => $type, //筛选现金桌或信用桌
            //'is_closed'     => 1, //捞已过结束时间
            'page_size'     => $this->page_size, //每页几条, 先写死防止被送1000条以上
            'page'          => $page, //第几页
            'append'        => ['start_time_text', 'end_time_text', 'create_time_text'], //扩充字段
            //'count'         => 1, //是否返回总条数
            'next_page'     => 1, //是否有下一页
        ];
        $rows = $this->repo_game_table->get_list($conds); //获取分页数据

        //获取桌子最新牌局记录时间
        $table_settle_time = $this->repo_winloss->get_list([
            'fields'    => [
                'table_id',
                DB::raw('MAX(settle_time) AS settle_time')
            ],
            'uid'       => [$uid],
            'index'     => 'table_id',
            'group_by'  => ['table_id'],
        ]);

        //获取该玩家下注过桌子总输赢
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'uid'       => $uid,
            'table_id'  => sql_in($rows['lists'], 'id'), //获取桌子id
        ]);
        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $rows['lists'][$k]['settle_time_text'] = isset($table_settle_time[$v['id']]) ?
                Carbon::createFromTimestamp($table_settle_time[$v['id']]['settle_time'])->format('Y-m-d H:i') : '';
            $rows['lists'][$k]['total_winloss_amount'] = isset($table_winloss[$v['id']]) ?
                $table_winloss[$v['id']]['total_winloss_amount'] : '0.00';
        }

        //区分桌号是现金桌或信用桌
        $conds = [
            'fields'        => ['id', 'type'],
            'id'            => $table_ids,
        ];
        $table_rows = $this->repo_game_table->get_list($conds); //获取全部数据
        $cash_table_ids = []; //现金桌桌号
        $credit_table_ids = []; //信用桌桌号
        foreach($table_rows as $v) //格式化数据
        {
            $v['type'] == 1 and $cash_table_ids[] = $v['id'];
            $v['type'] == 2 and $credit_table_ids[] = $v['id'];
        }

        //获取该玩家下注过桌子总输赢
        $total_cash_table_winloss_amount = 0;
        $total_credit_table_winloss_amount = 0;
        $total_winloss_amount = 0;
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'uid'       => $uid,
            'table_id'  => sql_in($table_rows, 'id'), //获取桌子id
        ]);
        foreach($table_winloss as $k => $v) //格式化数据
        {
            if(in_array($k, $cash_table_ids))
            {
                $total_cash_table_winloss_amount += $v['total_winloss_amount'];
                $total_winloss_amount += $total_cash_table_winloss_amount;
            }
            elseif(in_array($k, $credit_table_ids))
            {
                $total_credit_table_winloss_amount += $v['total_winloss_amount'];
                $total_winloss_amount += $total_credit_table_winloss_amount;
            }
        }
        //所有现金桌输赢
        $rows['total_cash_table_winloss_amount'] = money($total_cash_table_winloss_amount, '');
        //所有信用房输赢
        $rows['total_credit_table_winloss_amount'] = money($total_credit_table_winloss_amount, '');
        //所有桌总输赢
        $rows['total_winloss_amount'] = money($total_winloss_amount, '');

        return res_success($rows);
    }
}
