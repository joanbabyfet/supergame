<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_agent;
use App\repositories\repo_agent_oplog;
use App\repositories\repo_module;
use App\services\serv_array;
use Illuminate\Http\Request;

/**
 * 渠道代理操作日志控制器
 * Class ctl_agent_oplog
 * @package App\Http\Controllers\admin
 */
class ctl_agent_oplog extends Controller
{
    private $repo_agent_oplog;
    private $repo_module;
    private $repo_agent;
    private $serv_array;

    public function __construct(
        repo_agent_oplog $repo_agent_oplog,
        repo_module $repo_module,
        repo_agent $repo_agent,
        serv_array $serv_array
    )
    {
        parent::__construct();
        $this->repo_agent_oplog = $repo_agent_oplog;
        $this->repo_module = $repo_module;
        $this->repo_agent = $repo_agent;
        $this->serv_array = $serv_array;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        //$username   = $request->input('username');
        $module_id  = $request->input('module_id');
        $uid        = $request->input('uid');
        $page_size  = $request->input('page_size', $this->repo_agent_oplog->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start');
        $date_end   = $request->input('date_end');
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            //'username'   =>  $username,
            'module_id'     =>  $module_id,
            'uid'           =>  $uid,
            'date_start' =>  $date_start,
            'date_end'  =>   $date_end,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'append'        => ['op_time_text'], //扩充字段
            'count'     => 1, //是否返回总条数
        ];
        $rows = $this->repo_agent_oplog->get_list($conds);

        //获取模块列表
        $modules = $this->repo_module->get_list([]);
        $module_ids = $this->serv_array->one_array($modules, ['id', 'name']);

        //获取代理列表
        $agents = $this->repo_agent->get_list([]);
        $agent_ids = $this->serv_array->one_array($agents, ['id', 'realname']);

        foreach($rows['lists'] as $k => $v)
        {
            $row_plus = [
                'module_name' => $module_ids[$v['module_id']] ?? '',
                'op_user_text' => $agent_ids[$v['uid']] ?? ''
            ];
            $rows['lists'][$k] = array_merge($v, $row_plus);
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

        $status = $this->repo_agent_oplog->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_agent_oplog->get_err_msg($status), $status);
        }
        return res_success([], trans('api.api_delete_success'));
    }
}
