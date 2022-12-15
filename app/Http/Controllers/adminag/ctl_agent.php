<?php

namespace App\Http\Controllers\adminag;

use App\Models\mod_agent;
use App\repositories\repo_agent_oplog;
use App\repositories\repo_agent;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_agent extends Controller
{
    private $repo_agent;
    private $repo_agent_oplog;
    private $serv_util;
    private $module_id;

    public function __construct(
        repo_agent $repo_agent,
        repo_agent_oplog $repo_agent_oplog,
        serv_util $serv_util
    )
    {
        parent::__construct();
        $this->repo_agent               = $repo_agent;
        $this->repo_agent_oplog         = $repo_agent_oplog;
        $this->serv_util                = $serv_util;
        $this->module_id                = 22;
    }

    /**
     * 获取子帐号列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword', '');
        $date_start = $request->input('date_start'); //创建开始时间
        $date_end   = $request->input('date_end'); //创建结束时间
        //$realname  = $request->input('realname', '');
        //$username  = $request->input('username', '');
        $page_size  = $request->input('page_size', $this->repo_agent->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'keyword'       => $keyword,
            'pid'           => $this->pid,
            //'realname'      => $realname,
            //'username'      => $username,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['status_text', 'create_time_text'], //扩充字段
            'load'          => ['create_user_maps', 'role_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_agent->get_list($conds);

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
     * 获取子帐号详情
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
        $row = $this->repo_agent->find(['where' => [
            ['id', '=', $id],
            ['pid', '=', $this->pid],
        ]]);
        $row = empty($row) ? []:$row->toArray();
        return res_success($row);
    }

    /**
     * 添加子帐号
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        $status = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("子帐号添加 ", $this->module_id);

        return res_success([], trans('api.api_add_success'));
    }

    /**
     * 修改子帐号
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
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("子帐号修改 {$id}", $this->module_id);

        return res_success([], trans('api.api_update_success'));
    }

    /**
     * 保存子帐号
     * @version 1.0.0
     * @param Request $request
     * @return int|mixed
     * @throws \Throwable
     */
    private function save(Request $request)
    {
        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_agent->save([
            'do'            => get_action(),
            'id'            => $request->input('id'),
            'pid'           => $this->pid,
            'username'      => $request->input('username'),
            'password'      => $request->input('password'),
            'realname'      => $request->input('realname', ''),
            'role_id'       => $request->input('role_id', config('global.role_sub_account')),
            'desc'          => $request->input('desc', ''),
            'currency'      => $request->input('currency', config('global.currency')),
            'status'        => $request->input('status', mod_agent::ENABLE),
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
     * 删除子帐号
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('ids', []);

        $status = $this->repo_agent->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("子帐号刪除 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }

    /**
     * 开启子帐号
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Request $request)
    {
        $id     = $request->input('ids', []);
        $status = $this->repo_agent->change_status([
            'id'        => $id,
            'status'    => mod_agent::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("子帐号启用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_enable_success'));
    }

    /**
     * 禁用子帐号
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Request $request)
    {
        $id = $request->input('ids', []);
        $status = $this->repo_agent->change_status([
            'id'        => $id,
            'status'    => mod_agent::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        // 批量强制退出登录
        foreach ($id as $v)
        {
            //干掉用户信息缓存
            $this->repo_agent->del_cache($v);
            //干掉token缓存
            $token = $this->repo_agent->get_token_by_uid($v);
            $this->repo_agent->unbind_token_uid($token, $v);
            //将该token放入黑名单
            $token and auth($this->guard)->setToken($token)->invalidate();
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("子帐号禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
