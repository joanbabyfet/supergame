<?php

namespace App\Http\Controllers\api;

use App\repositories\repo_agent;
use App\repositories\repo_app_key;
use App\repositories\repo_user;
use App\repositories\repo_winloss;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ctl_report extends Controller
{
    private $repo_winloss;
    private $repo_agent;
    private $repo_app_key;
    private $repo_user;

    public function __construct(
        repo_winloss $repo_winloss,
        repo_agent $repo_agent,
        repo_app_key $repo_app_key,
        repo_user $repo_user
    )
    {
        parent::__construct();
        $this->repo_winloss     = $repo_winloss;
        $this->repo_agent       = $repo_agent;
        $this->repo_app_key     = $repo_app_key;
        $this->repo_user        = $repo_user;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $app_id = $request->input('app_id', '');
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');

        if(empty($app_id) || empty($date_start) || empty($date_end))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //检测日期区间限制, lt=小于
        if(Carbon::parse($date_start)->addMonths(1)->lt(Carbon::parse($date_end)))
        {
            return res_error('日期间隔不能大于一个月', -2);
        }

        //通过应用id获取代理id
        $agent_id = $this->repo_app_key->get_field_value([
            'fields' => ['agent_id'],
            'where' => [
                ['app_id', '=', $app_id]
            ]
        ]);
        //获取代理信息
        $row = $this->repo_agent->find(['where' => [['id', '=', $agent_id]]]);

        $page_size  = $request->input('page_size', $this->repo_winloss->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            'agent_id'      => $row['id'],
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['settle_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_winloss->get_list($conds);

        //获取玩家列表
        $users = $this->repo_user->get_list([
            'index'     => 'id',
            'agent_id'  => $row['id']
        ]);

        foreach($rows['lists'] as $k => $v)
        {
            $row_plus = [
                'username' => $users[$v['uid']]['username'] ?? '', //玩家帐号
            ];
            $rows['lists'][$k] = array_merge($v, $row_plus);
        }
        return res_success($rows);
    }
}
