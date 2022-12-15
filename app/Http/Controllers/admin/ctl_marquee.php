<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_marquee;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_marquee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 跑马灯
 * Class ctl_marquee
 * @package App\Http\Controllers\admin
 */
class ctl_marquee extends Controller
{
    private $repo_marquee;
    private $repo_admin_user_oplog;
    private $module_id;

    public function __construct(
        repo_marquee $repo_marquee,
        repo_admin_user_oplog $repo_admin_user_oplog
    )
    {
        parent::__construct();
        $this->repo_marquee          = $repo_marquee;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->module_id = 13;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status     = $request->input('status');
        $page_size  = $request->input('page_size', $this->repo_marquee->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'status'    => $status,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'append'    => ['status_text', 'create_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_marquee->get_list($conds);
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
        $row = $this->repo_marquee->find(['where' => [['id', '=', $id]]]);
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
            return res_error($this->repo_marquee->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_marquee->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("跑马灯添加 ", $this->module_id);

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
            return res_error($this->repo_marquee->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_marquee->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("跑马灯修改 {$id}", $this->module_id);

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
        $status = $this->repo_marquee->save([
            'do'        => get_action(),
            'id'        => $request->input('id'),
            'cat_id'    => $request->input('cat_id', 0),
            'content'   => $request->input('content', ''),
            'sort'      => $request->input('sort', 0),
            'status'    => $request->input('status', mod_marquee::ENABLE),
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

        $status = $this->repo_marquee->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_marquee->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_marquee->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("跑马灯刪除 {$id}", $this->module_id);

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
        $status = $this->repo_marquee->change_status([
            'id'        => $id,
            'status'    => mod_marquee::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_marquee->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_marquee->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("跑马灯启用 ".implode(",", $id), $this->module_id);

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
        $status = $this->repo_marquee->change_status([
            'id'        => $id,
            'status'    => mod_marquee::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_marquee->get_err_msg($status), $status);
        }
        //更新缓存
        $this->repo_marquee->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("跑马灯禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
