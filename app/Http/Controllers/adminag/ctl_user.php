<?php

namespace App\Http\Controllers\adminag;

use App\Models\mod_user;
use App\repositories\repo_agent_oplog;
use App\repositories\repo_user;
use App\repositories\repo_user_login_log;
use App\services\serv_rpc_client;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_user extends Controller
{
    private $repo_user;
    private $repo_agent_oplog;
    private $serv_util;
    private $repo_user_login_log;
    private $serv_rpc_client;
    private $module_id;

    public function __construct(
        repo_user $repo_user,
        repo_agent_oplog $repo_agent_oplog,
        serv_util $serv_util,
        repo_user_login_log $repo_user_login_log,
        serv_rpc_client $serv_rpc_client
    )
    {
        parent::__construct();
        $this->repo_user                = $repo_user;
        $this->repo_agent_oplog    = $repo_agent_oplog;
        $this->serv_util                = $serv_util;
        $this->repo_user_login_log      = $repo_user_login_log;
        $this->serv_rpc_client          = $serv_rpc_client;
        $this->module_id = 1;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $origin     = $request->input('origin'); //注册来源
        $page_size  = $request->input('page_size', $this->repo_user->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //注册开始时间
        $date_end   = $request->input('date_end'); //注册结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());
        $type  = $request->input('type'); //类型

        $conds = [
            'agent_id'      => $this->pid, //渠道代理
            'origin'        => $origin,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'type'          => $type,
            'status'        => mod_user::ENABLE, //已启用
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            //'fields' => ['id', 'cat_id'], //展示字段
            'append'        => ['origin_text', 'is_new_user_text',
                'status_text', 'create_time_text', 'login_time_text'], //扩充字段
            'load'          => ['agent_maps', 'wallet_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_user->get_list($conds);
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

    /**
     * 获取黑名单列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function black_list(Request $request)
    {
        $origin     = $request->input('origin'); //注册来源
        $page_size  = $request->input('page_size', $this->repo_user->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start'); //注册开始时间
        $date_end   = $request->input('date_end'); //注册结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            'agent_id'      => $this->pid, //渠道代理
            'origin'        => $origin,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'status'        => mod_user::DISABLE, //黑名单
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            //'fields' => ['id', 'cat_id'], //展示字段
            'append'        => ['origin_text', 'is_new_user_text',
                'status_text', 'create_time_text', 'login_time_text'], //扩充字段
            'load'          => ['agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_user->get_list($conds);
        return res_success($rows);
    }

    /**
     * 导出excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export(Request $request)
    {
        return $this->index($request);
    }

    /**
     * 获取详情
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        $id = $request->input('id');
        if(empty($id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }
        $row = $this->repo_user->find(['where' => [['id', '=', $id]]]);
        $row = empty($row) ? []:$row->toArray();
        return res_success($row);
    }

    /**
     * 添加
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        $status = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("用户添加 ", $this->module_id);

        return res_success([], trans('api.api_add_success'));
    }

    /**
     * 修改 (产品定义运营后台用户信息不给改)
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
//    public function edit(Request $request)
//    {
//        $id     = $request->input('id');
//        $status = $this->save($request);
//        if($status < 0)
//        {
//            return res_error($this->repo_user->get_err_msg($status), $status);
//        }
//        //寫入日志
//        $this->repo_agent_oplog->add_log("用户修改 {$id}", $this->module_id);
//
//        return res_success([], trans('api.api_update_success'));
//    }

    /**
     * 保存
     * @version 1.0.0
     * @param Request $request
     * @return int|mixed
     * @throws \Throwable
     */
    private function save(Request $request)
    {
        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_user->save([
            'do'        => get_action(),
            'id'        => $request->input('id'),
            'username'  => $request->input('username'),
            'password'  => $request->input('password'),
            'agent_id'  => $request->input('agent_id', ''),
            'realname'  => $request->input('realname', ''),
            'role_id'   => $request->input('role_id', config('global.role_general_member')),
            'status'    => $request->input('status', mod_user::ENABLE),
        ], $ret_data);

        if ($status > 0)
        {
            DB::commit(); //手動提交事务
        }
        else
        {
            DB::rollBack(); //手動回滚事务
        }
        return $status;
    }

    /**
     * 开启
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Request $request)
    {
        $id     = $request->input('id', '');
        $ban_desc   = $request->input('ban_desc', '');
        $status = $this->repo_user->change_status([
            'id'        => $id,
            'ban_desc'  => $ban_desc,
            'status'    => mod_user::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_user->get_err_msg($status), $status);
        }
        //通知游戏服 DELETED = 0, DISABLED = 1, ENABLED = 2
        $this->serv_rpc_client->change_user_status(['id' => $id, 'status' => 2]);
        //寫入日志
        $this->repo_agent_oplog->add_log("用户解封 {$id}", $this->module_id);

        return res_success([], trans('api.api_enable_success'));
    }

    /**
     * 禁用
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Request $request)
    {
        $id         = $request->input('id', '');
        $ban_desc   = $request->input('ban_desc', '');
        $status     = $this->repo_user->change_status([
            'id'        => $id,
            'ban_desc'  => $ban_desc,
            'status'    => mod_user::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_user->get_err_msg($status), $status);
        }
        //通知游戏服 DELETED = 0, DISABLED = 1, ENABLED = 2
        $this->serv_rpc_client->change_user_status(['id' => $id, 'status' => 1]);
        //寫入日志
        $this->repo_agent_oplog->add_log("用户封禁 {$id}", $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }

    /**
     * 获取登录日志
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login_log(Request $request)
    {
        $page_size  = $request->input('page_size', $this->repo_user->page_size);
        $page       = $request->input('page', 1);
        $uid        = $request->input('uid'); //用户id
        $date_start = $request->input('date_start'); //开始时间
        $date_end   = $request->input('date_end'); //结束时间
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        $conds = [
            'uid'           => $uid,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['status_text', 'login_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_user_login_log->get_list($conds);
        return res_success($rows);
    }
}
