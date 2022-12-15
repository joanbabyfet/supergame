<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_module;
use App\services\serv_array;
use Illuminate\Http\Request;

/**
 * 管理员操作日志控制器
 * Class ctl_admin_user_oplog
 * @package App\Http\Controllers\admin
 */
class ctl_admin_user_oplog extends Controller
{
    private $repo_admin_user_oplog;
    private $repo_module;
    private $repo_admin_user;
    private $serv_array;

    public function __construct(
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_module $repo_module,
        repo_admin_user $repo_admin_user,
        serv_array $serv_array
    )
    {
        parent::__construct();
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->repo_module = $repo_module;
        $this->repo_admin_user = $repo_admin_user;
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
        $page_size  = $request->input('page_size', $this->repo_admin_user_oplog->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start');
        $date_end   = $request->input('date_end');
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            //'username'      =>  $username,
            'module_id'     =>  $module_id,
            'uid'           =>  $uid,
            'date_start'    =>  $date_start,
            'date_end'      =>   $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['op_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_admin_user_oplog->get_list($conds);

        //获取模块列表
        $modules = $this->repo_module->get_list([]);
        $module_ids = $this->serv_array->one_array($modules, ['id', 'name']);

        //获取管理员列表
        $admin_users = $this->repo_admin_user->get_list([]);
        $admin_user_ids = $this->serv_array->one_array($admin_users, ['id', 'realname']);

        foreach($rows['lists'] as $k => $v)
        {
            $row_plus = [
                'module_name' => $module_ids[$v['module_id']] ?? '',
                'op_user_text' => $admin_user_ids[$v['uid']] ?? ''
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

        $status = $this->repo_admin_user_oplog->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_admin_user_oplog->get_err_msg($status), $status);
        }
        return res_success([], trans('api.api_delete_success'));
    }
}
