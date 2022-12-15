<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_user_login_log;
use Illuminate\Http\Request;

/**
 * 用户登入日志控制器
 * Class ctl_user_login_log
 * @package App\Http\Controllers\admin
 */
class ctl_user_login_log extends Controller
{
    private $repo_user_login_log;

    public function __construct(repo_user_login_log $repo_user_login_log)
    {
        parent::__construct();
        $this->repo_user_login_log = $repo_user_login_log;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $username   = $request->input('username');
        $page_size  = $request->input('page_size', $this->repo_user_login_log->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start');
        $date_end   = $request->input('date_end');
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            'username'   =>  $username,
            'date_start' =>  $date_start,
            'date_end'  =>   $date_end,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'count'     => 1, //是否返回总条数
        ];
        $rows = $this->repo_user_login_log->get_list($conds);
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

        $status = $this->repo_user_login_log->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_user_login_log->get_err_msg($status), $status);
        }
        return res_success([], trans('api.api_delete_success'));
    }
}
