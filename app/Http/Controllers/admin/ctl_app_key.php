<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_app_key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 代理私钥控制器 (暂未使用)
 * Class ctl_app_key
 * @package App\Http\Controllers\admin
 */
class ctl_app_key extends Controller
{
    private $repo_app_key;
    private $repo_admin_user_oplog;
    private $cache_time;
    private $module_id;

    public function __construct(
        repo_app_key $repo_app_key,
        repo_admin_user_oplog $repo_admin_user_oplog
    )
    {
        parent::__construct();
        $this->repo_app_key          = $repo_app_key;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->cache_time   = config('global.cache_time');
        $this->module_id = 24;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $app_id     = $request->input('app_id');
        $page_size  = $request->input('page_size', $this->repo_app_key->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'app_id'    => $app_id,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'append'    => ['create_time_text'], //扩充字段
            'load'      => ['agent_maps'],
            'count'     => 1, //是否返回总条数
        ];
        $rows = $this->repo_app_key->get_list($conds);
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
        $id = $request->input('app_id');
        if(empty($id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

//        $cache_key = sprintf($this->repo_app_key->detail_cache_key, $id);
//        $row = Redis::get($cache_key);
//        if(empty($row))
//        {
//            $row = $this->repo_app_key->find(['where' => [['app_id', '=', $id]]]);
//            Redis::setex($cache_key, $this->cache_time, json_encode($row, JSON_UNESCAPED_UNICODE));
//        }
        $row = $this->repo_app_key->find(['where' => [['app_id', '=', $id]]]);
        $row = empty($row) ? []: $row->toArray();
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
        $app_id    = $request->input('app_id');
        $status     = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_app_key->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log(AUTH_UID."应用私匙添加 ".$app_id, $this->module_id);

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
        $id     = $request->input('app_id');
        $status = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_app_key->get_err_msg($status), $status);
        }
        //干掉緩存
//        $cache_key = sprintf($this->repo_app_key->detail_cache_key, $id);
//        !empty($id) and Redis::del($cache_key);

        //寫入日志
        $this->repo_admin_user_oplog->add_log(AUTH_UID."应用私匙修改 {$id}", $this->module_id);

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
        $data = [];
        $data['do'] = get_action();
        $request->has('app_id') and $data['app_id'] = $request->input('app_id');
        $request->has('app_key') and $data['app_key'] = $request->input('app_key');
        $request->has('agent_id') and $data['agent_id'] = $request->input('agent_id');
        $request->has('desc') and $data['desc'] = $request->input('desc');

        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_app_key->save($data, $ret_data);
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
        $id = $request->input('app_id');

        $status = $this->repo_app_key->del(['app_id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_app_key->get_err_msg($status), $status);
        }
        //干掉緩存
//        $cache_key = sprintf($this->repo_app_key->detail_cache_key, $id);
//        !empty($id) and Redis::del($cache_key);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("应用私匙刪除 {$id}", $this->module_id);

        return res_success([], trans('api.api_delete_success'));
    }

    /**
     * 生成 app key
     */
    public function generate_app_key()
    {
        $key_data = $this->repo_app_key->create_app_key();
        return res_success($key_data);
    }
}
