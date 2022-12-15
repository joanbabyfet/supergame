<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\mod_example;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_example;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * 文章控制器 (范例) - 使用缓存
 * 用php序列化其他语言又不能读取，所以这里用json
 * Class ctl_example_redis
 * @package App\Http\Controllers\admin
 */
class ctl_example_redis extends Controller
{
    private $repo_example;
    private $repo_admin_user_oplog;
    private $cache_time;
    private $serv_util;
    private $module_id;

    public function __construct(
        repo_example $repo_example,
        repo_admin_user_oplog $repo_admin_user_oplog,
        serv_util $serv_util)
    {
        parent::__construct();
        $this->repo_example = $repo_example;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->cache_time   = config('global.cache_time');
        $this->serv_util           = $serv_util;
        $this->module_id = 21;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $title      = $request->input('title');
        $status     = $request->input('status');
        $page_size  = get_action() == 'export' ? 100 :
            $request->input('page_size', $this->repo_example->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'title'     => $title,
            'status'    => $status,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'count'     => 1, //是否返回总条数
            //'fields' => ['id', 'cat_id'], //展示字段
            //'append' => [], //扩充字段
        ];
        $rows = $this->repo_example->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size);

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
                'page_no'   => $page,
                'rows'      => $rows['lists'],
                'file'      => $request->input('file', ''),
                'fields'    => $request->input('fields', []), //列表所有字段
                'titles'    => $titles, //輸出字段
                'total_page' => $total_page,
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

        $cache_key = sprintf($this->repo_example->detail_cache_key, $id);
        $row = Redis::get($cache_key);
        if(empty($row))
        {
            $row = $this->repo_example->find(['where' => [['id', '=', $id]]]);
            Redis::setex($cache_key, $this->cache_time, json_encode($row, JSON_UNESCAPED_UNICODE));
        }

        $row = empty($row) ? []: json_decode($row, true);
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
            return res_error($this->repo_example->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("文章添加 ", $this->module_id);

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
            return res_error($this->repo_example->get_err_msg($status), $status);
        }
        //干掉緩存
        $cache_key = sprintf($this->repo_example->detail_cache_key, $id);
        !empty($id) and Redis::del($cache_key);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("文章修改 {$id}", $this->module_id);

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
        $status = $this->repo_example->save([
            'do'        => get_action(),
            'id'        => $request->input('id'),
            'cat_id'    => $request->input('cat_id'),
            'title'     => $request->input('title'),
            'content'   => $request->input('content', ''),
            'img'       => $request->input('img', ''),
            'sort'      => $request->input('sort', 0),
            'status'    => $request->input('status', mod_example::ENABLE),
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

        $status = $this->repo_example->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_example->get_err_msg($status), $status);
        }
        //干掉緩存
        $cache_key = sprintf($this->repo_example->detail_cache_key, $id);
        !empty($id) and Redis::del($cache_key);

        //寫入日志
        $this->repo_admin_user_oplog->add_log("文章刪除 {$id}", $this->module_id);

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
        $status = $this->repo_example->change_status([
            'id'        => $id,
            'status'    => mod_example::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_example->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("文章启用 ".implode(",", $id), $this->module_id);

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
        $status = $this->repo_example->change_status([
            'id'        => $id,
            'status'    => mod_example::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_example->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("文章禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
