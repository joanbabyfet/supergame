<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_menu;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_menu;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ctl_agent_menu extends Controller
{
    private $repo_menu;
    private $repo_admin_user_oplog;
    private $serv_util;
    private $module_id;

    public function __construct(
        repo_menu $repo_menu,
        repo_admin_user_oplog $repo_admin_user_oplog,
        serv_util $serv_util
    )
    {
        parent::__construct();
        $this->repo_menu                = $repo_menu;
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
        $this->serv_util                = $serv_util;
        $this->module_id = 26;
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
        $page_size  = $request->input('page_size', $this->repo_menu->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'guard_name'    => config('global.adminag.guard'),
            'name'          => $name,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['status_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_menu->get_list($conds);
        return res_success($rows);
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
            return res_error($this->repo_menu->get_err_msg($status), $status);
        }
        //干掉緩存
        Redis::del($this->repo_menu->cache_key_agent);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("菜单添加 ", $this->module_id);

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
            return res_error($this->repo_menu->get_err_msg($status), $status);
        }
        //干掉緩存
        Redis::del($this->repo_menu->cache_key_agent);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("菜单修改 {$id}", $this->module_id);

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
        $status = $this->repo_menu->save([
            'do'                => get_action(),
            'id'                => $request->input('id'),
            'parent_id'         => $request->input('parent_id'),
            'name'              => $request->input('name', ''),
            'type'              => $request->input('type', 1),
            'guard_name'        => config('global.adminag.guard'),
            'url'               => $request->input('url', ''),
            'icon'              => $request->input('icon', ''),
            'perms'             => $request->input('perms', ''),
            'sort'              => $request->input('sort', 0),
            'is_show'           => $request->input('is_show', 1),
            'status'            => $request->input('status', mod_menu::ENABLE),
        ]);

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

        $status = $this->repo_menu->del(['id' => $id + [-1]]);
        if($status < 0)
        {
            return res_error($this->repo_menu->get_err_msg($status), $status);
        }
        //干掉緩存
        Redis::del($this->repo_menu->cache_key_agent);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("菜单刪除 ".implode(',', $id), $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }
}
