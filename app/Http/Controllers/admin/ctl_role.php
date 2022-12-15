<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_admin_user;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_model_has_roles;
use App\repositories\repo_role;
use App\services\serv_permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ctl_role extends Controller
{
    private $repo_role;
    private $repo_admin_user_oplog;
    private $repo_model_has_roles;
    private $serv_permission;
    private $module_id;

    public function __construct(
        repo_role $repo_role,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_model_has_roles $repo_model_has_roles,
        serv_permission $serv_permission
    )
    {
        parent::__construct();
        $this->repo_role = $repo_role;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->repo_model_has_roles = $repo_model_has_roles;
        $this->serv_permission = $serv_permission;
        $this->module_id = 17;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $name       = $request->input('name');
        $page_size  = $request->input('page_size', $this->repo_role->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'name'          =>  $name,
            'guard_name'    => config('global.admin.guard'),
            'page_size'     => $page_size, //每页几条
            //'append'      => [], //扩充字段
            'with_count'    => ['users'], //统计子关联记录的条数
            'count'         => 1, //是否返回总条数
        ];
        $request->has('page') and $conds['page'] = $page; //第几页, 与下拉选项共用同接口

        $rows = $this->repo_role->get_list($conds);
        return res_success($rows);
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
        $row = $this->repo_role->find(['where' => [['id', '=', $id]]]);
        $row = empty($row) ? [] : $row->toArray();
        if(!empty($row))
        {
            //获取权限树
            $permissions = $this->serv_permission->get_tree([
                'guard'    => $row['guard_name'],
                'order_by' => ['created_at', 'asc'],
                'is_auth'  => 1,
            ]);
            //获取组权限
            $role = Role::findById($id, $row['guard_name']);
            //返回一维数组,格式[4,5,6]
            $purviews = $role->permissions()->pluck('id')->toArray();
        }

        $data = array_merge($row, [
            'permissions'   =>  $permissions ?? [],
            'purviews'      =>  $purviews ?? [],
        ]);
        return res_success($data);
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
            return res_error($this->repo_role->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("角色添加 ", $this->module_id);

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
            return res_error($this->repo_role->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("角色修改 {$id}", $this->module_id);

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
        $status = $this->repo_role->save([
            'do'            => get_action(),
            'id'            => $request->input('id'),
            'name'          => $request->input('name', ''),
            'guard_name'    => config('global.admin.guard'),
            'permissions'   => $request->input('permissions') ?? [],
            'desc'          => $request->input('desc', ''),
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
        $id = $request->input('id');
        if(empty($id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //该角色有帐号不可删
        $exists = $this->repo_model_has_roles->get_count(['where' => [
            ['role_id', '=', $id],
            ['model_type', '=', get_class(new mod_admin_user())]
        ]]);
        if($exists)
        {
            return res_error('该角色下面存在帐号，不可删除', -2);
        }

        $status = $this->repo_role->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_role->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("角色刪除 {$id}", $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }

    /**
     * 获取权限列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function permission_list(Request $request)
    {
        $rows = $this->serv_permission->get_tree([
            'guard'    => config('global.admin.guard'),
            'order_by' => ['created_at', 'asc'],
            'is_auth'  => 1,
        ]);
        return res_success($rows);
    }
}
