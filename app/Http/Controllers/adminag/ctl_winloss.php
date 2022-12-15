<?php

namespace App\Http\Controllers\adminag;

use App\repositories\repo_agent_oplog;
use App\repositories\repo_game_table;
use App\repositories\repo_user;
use App\repositories\repo_winloss;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_winloss extends Controller
{
    private $repo_winloss;
    private $repo_agent_oplog;
    private $repo_game_table;
    private $repo_user;
    private $serv_util;
    private $module_id;

    public function __construct(
        repo_winloss $repo_winloss,
        repo_agent_oplog $repo_agent_oplog,
        repo_game_table $repo_game_table,
        repo_user $repo_user,
        serv_util $serv_util
    )
    {
        parent::__construct();
        $this->repo_winloss             = $repo_winloss;
        $this->repo_agent_oplog         = $repo_agent_oplog;
        $this->repo_game_table          = $repo_game_table;
        $this->repo_user                = $repo_user;
        $this->serv_util                = $serv_util;
        $this->module_id                = 16;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword', '');
        $round_id   = $request->input('round_id', ''); //局号id
        $page_size  = $request->input('page_size', $this->repo_winloss->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //注册开始时间
        $date_end   = $request->input('date_end'); //注册结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        //获取用户id
        $uids = [];
        if($keyword)
        {
            $users = $this->repo_user->lists(['where' => [
                [DB::raw('CONCAT(username, realname)'), 'like', "%{$keyword}%"],
            ]])->toArray();
            $uids = sql_in($users, 'id');
        }

        $conds = [
            'uid'           => $uids,
            'round_id'      => $round_id,
            'agent_id'      => $this->pid,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['bet_type_text', 'settle_time_text'], //扩充字段
            'load'          => ['user_maps', 'round_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_winloss->get_list($conds);

        //获取玩家列表
        $users = $this->repo_user->get_list([
            'index' => 'id',
        ]);
        //获取桌子列表
        $tables = $this->repo_game_table->get_list([
            'index'     => 'id',
        ]);
        foreach($rows['lists'] as $k => $v)
        {
            $table_owner_uid = $tables[$v['table_id']]['uid'] ?? '';
            $row_plus = [
                'table_owner_text' => $users[$table_owner_uid]['realname'] ?? '',
            ];
            $rows['lists'][$k] = array_merge($v, $row_plus);
        }
        $total_page = ceil($rows['total'] / $page_size); //总页数

        if(get_action() == 'export')
        {
            $titles = [
                'title'             => '標題',
                'content'           => '内容',
                'sort'              => '排序',
                'status_text'       => '狀態',
                'create_time_text'  => '添加時間',
            ];

            $status = $this->serv_util->export_data([
                'page_no'       => $page,
                'rows'          => $rows['lists'],
                'file'          => $request->input('file', ''),
                'fields'        => $request->input('fields', []), //列表所有字段
                'titles'        => $titles, //輸出字段
                'total_page'    => $total_page,
            ], $ret_data);
            if($status < 0)
            {
                return res_error($this->serv_util->get_err_msg($status), $status);
            }
            return res_success($ret_data);
        }

        return res_success($rows);
    }
}
