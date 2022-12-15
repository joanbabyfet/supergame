<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 系统配置控制器
 * Class ctl_config
 * @package App\Http\Controllers\admin
 */
class ctl_config extends Controller
{
    private $repo_config;
    private $repo_admin_user_oplog;
    private $module_id;

    public function __construct(repo_config $repo_config, repo_admin_user_oplog $repo_admin_user_oplog)
    {
        parent::__construct();
        $this->repo_config          = $repo_config;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->module_id = 25;
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
        $group      = $request->input('group');
        $page_size  = $request->input('page_size', $this->repo_config->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'name'     => $name,
            'group'    => $group,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'count'     => 1, //是否返回总条数
        ];
        $rows = $this->repo_config->get_list($conds);
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
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("配置添加 ", $this->module_id);

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
        $name     = $request->input('name');
        $status = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("配置修改 {$name}", $this->module_id);

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
        $status = $this->repo_config->save([
            'do'        => get_action(),
            'type'      => $request->input('type', ''),
            'name'      => $request->input('name', ''),
            'value'     => $request->input('value', ''),
            'title'     => $request->input('title', ''),
            'group'     => $request->input('group', ''),
            'sort'      => $request->input('sort', 0),
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
        $id = $request->input('ids');

        $status = $this->repo_config->del(['name' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_config->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_config->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("配置刪除 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }
}
