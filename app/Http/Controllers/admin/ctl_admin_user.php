<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_admin_user;
use App\repositories\repo_admin_user;
use App\repositories\repo_admin_user_login_log;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_model_has_roles;
use App\services\serv_array;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 管理员控制器
 * Class ctl_admin_user
 * @package App\Http\Controllers\admin
 */
class ctl_admin_user extends Controller
{
    private $repo_admin_user;
    private $repo_admin_user_oplog;
    private $repo_admin_user_login_log;
    private $repo_model_has_roles;
    private $serv_util;
    private $serv_array;
    private $module_id;

    public function __construct(
        repo_admin_user $repo_admin_user,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_admin_user_login_log $repo_admin_user_login_log,
        repo_model_has_roles $repo_model_has_roles,
        serv_util $serv_util,
        serv_array $serv_array
    )
    {
        parent::__construct();
        $this->repo_admin_user              = $repo_admin_user;
        $this->repo_admin_user_oplog        = $repo_admin_user_oplog;
        $this->repo_admin_user_login_log    = $repo_admin_user_login_log;
        $this->repo_model_has_roles         = $repo_model_has_roles;
        $this->serv_util                    = $serv_util;
        $this->serv_array                   = $serv_array;
        $this->module_id                    = 18;
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
        $role_id    = $request->input('role_id', '');
        $page_size  = $request->input('page_size', $this->repo_admin_user->page_size);
        $page       = $request->input('page', 1);

        //获取某角色用户
        $users = $this->repo_model_has_roles->get_list([
            'role_id'       => $role_id,
            'model_type'    => get_class(new mod_admin_user())
        ]);
        $uids = $this->serv_array->sql_in($users, 'model_id');

        $conds = [
            'uid'           => $uids,
            'keyword'       => $keyword,
            'role_id'       => $role_id,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['status_text', 'create_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
            'load'          => ['create_user_maps', 'role_maps'],
        ];
        $rows = $this->repo_admin_user->get_list($conds);
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
        $row = $this->repo_admin_user->find(['where' => [['id', '=', $id]]]);
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
            return res_error($this->repo_admin_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("用户添加 ", $this->module_id);

        return res_success([], trans('api.api_add_success'));
    }

    /**
     * 修改
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $id     = $request->input('id');
        $status = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_admin_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("用户修改 {$id}", $this->module_id);

        return res_success([], trans('api.api_update_success'));
    }

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
        $status = $this->repo_admin_user->save([
            'do'        => get_action(),
            'id'        => $request->input('id'),
            'username'  => $request->input('username', ''),
            'password'  => $request->input('password', ''),
            'realname'  => $request->input('realname', ''),
            //来自浏览器的一般只会出现0,null,''这三种，过滤其二则可，因为0不是空数据
            'roles'     => array_filter($request->input('roles', [])),
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
     * 删除
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('ids', []);

        $status = $this->repo_admin_user->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_admin_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("用户刪除 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }

    /**
     * 开启
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Request $request)
    {
        $id     = $request->input('ids', []);
        $status = $this->repo_admin_user->change_status([
            'id'        => $id,
            'status'    => mod_admin_user::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_admin_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("用户启用 ".implode(",", $id), $this->module_id);

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
        $id = $request->input('ids', []);
        $status = $this->repo_admin_user->change_status([
            'id'        => $id,
            'status'    => mod_admin_user::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_admin_user->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("用户禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
