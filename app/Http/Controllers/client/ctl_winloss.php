<?php

namespace App\Http\Controllers\client;

use App\repositories\repo_game_table;
use App\repositories\repo_winloss;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_winloss extends Controller
{
    private $repo_winloss;
    private $repo_game_table;
    private $page_size;

    public function __construct(
        repo_winloss $repo_winloss,
        repo_game_table $repo_game_table
    )
    {
        parent::__construct();
        $this->repo_winloss             = $repo_winloss;
        $this->repo_game_table          = $repo_game_table;
        $this->page_size                = 10;
    }

    /**
     * 获取玩家牌局记录列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $table_id   = $request->input('table_id', '');
        $uid        = $request->input('uid', '');
        //$page_size  = $request->input('page_size', $this->repo_winloss->page_size);
        $page       = $request->input('page', 1);

        if(empty($uid) || empty($table_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        $conds = [
            'uid'           => [$uid],
            'table_id'      => $table_id,
            'page_size'     => $this->page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['bet_type_text', 'settle_time_text', 'is_sz_text'], //扩充字段
            'load'          => ['round_maps'],
            //'count'         => 1, //是否返回总条数
            'next_page'     => 1, //是否有下一页
        ];
        $rows = $this->repo_winloss->get_list($conds);

        //获取桌主uid
        $table_owner_uid    = $this->repo_game_table->get_field_value([
            'fields'    => ['uid'],
            'where' => [['id', '=', $table_id]]
        ]);

        //获取该玩家(非桌主)下注过桌子总输赢
        $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'uid'       => $uid,
            'table_id'  => [$table_id], //获取桌子id
        ]);
        //累计输赢(全部)
        $rows['total_winloss_amount'] = isset($table_winloss[$table_id]) ?
            $table_winloss[$table_id]['total_winloss_amount'] : '0.00';

        //获取该桌子id所有玩家全部输赢
        $table_all_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => [$table_id], //获取桌子id
        ]);
        $total_table_owner_commission = isset($table_all_winloss[$table_id]) ?
            $table_all_winloss[$table_id]['total_table_owner_commission'] : '0.00';
        //累计抽水(全部)
        if($table_owner_uid == $uid) //桌主才展示总抽水
        {
            $rows['total_commission'] = money($total_table_owner_commission, '');
        }
        return res_success($rows);
    }

    /**
     * 获取某牌桌所有玩家列表(去重)
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_table_user_list(Request $request)
    {
        $table_id   = $request->input('table_id', '');
        //$page_size  = $request->input('page_size', $this->repo_winloss->page_size);
        $page       = $request->input('page', 1);

        if(empty($table_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        $conds = [
            'field'         => DB::raw('DISTINCT uid'), //分组时总条数才会正确
            'fields'        => [
                'uid',
                'table_id',
                DB::raw('SUM(winloss_amount) AS winloss_amount'),
            ],
            'table_id'      => $table_id,
            'page_size'     => $this->page_size, //每页几条, 先写死防止被送1000条以上
            'page'          => $page, //第几页
            'group_by'      => ['uid', 'table_id'],
            'order_by'      => ['winloss_amount', 'desc'],
            'load'          => ['user_maps'],
            //'count'         => 1, //是否返回总条数
            'next_page'     => 1, //是否有下一页
        ];
        $rows = $this->repo_winloss->get_list($conds);
        return res_success($rows);
    }
}
