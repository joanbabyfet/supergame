<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_room;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_game;
use App\repositories\repo_room;
use App\services\serv_rpc_client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_room extends Controller
{
    private $repo_room;
    private $repo_admin_user_oplog;
    private $repo_game;
    private $serv_rpc_client;
    private $module_id;

    public function __construct(
        repo_room $repo_room,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_game $repo_game,
        serv_rpc_client $serv_rpc_client
    )
    {
        parent::__construct();
        $this->repo_room                = $repo_room;
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
        $this->repo_game                = $repo_game;
        $this->serv_rpc_client          = $serv_rpc_client;
        $this->module_id = 8;
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
        $page_size  = $request->input('page_size', $this->repo_room->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'status'    => $status,
            'page_size' => $page_size, //每页几条
            'append'    => ['status_text', 'create_time_text', 'cover_img_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $request->has('page') and $conds['page'] = $page; //第几页, 与下拉选项共用同接口

        $rows = $this->repo_room->get_list($conds);
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
        $row = $this->repo_room->find([
            'where' => [['id', '=', $id]],
            'append'    => ['status_text', 'create_time_text', 'cover_img_text'],
        ]);
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
        $cover_img              = $request->input('cover_img', '');
        [$status, $ret_data]    = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_room->get_err_msg($status), $status);
        }
        //更新缓存
        //$this->repo_room->cache(true);
        //通知游戏服
        $row = $this->repo_game->get_game_by_code('PG'); //根据游戏代码获取游戏信息
        $game_id = $row ? $row['id'] : '';

        $this->serv_rpc_client->create_room([
            'id'            => $ret_data['id'],
            'game_id'       => $game_id,
            'name'          => $request->input('name', ''),
            'cover_img'     => empty($cover_img) ? '' : display_img($cover_img),
            'video_url'     => $request->input('video_url', ''),
            'desc'          => $request->input('desc', ''),
            'sort'          => $request->input('sort', 0),
            'status'        => $request->input('status', mod_room::ENABLE),
        ]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("房间添加 ", $this->module_id);

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
        $id                     = $request->input('id');
        $cover_img              = $request->input('cover_img', '');
        [$status, $ret_data]    = $this->save($request);
        if($status < 0)
        {
            return res_error($this->repo_room->get_err_msg($status), $status);
        }
        //更新缓存
        //$this->repo_room->cache(true);
        //通知游戏服
        $row = $this->repo_game->get_game_by_code('PG'); //根据游戏代码获取游戏信息
        $game_id = $row ? $row['id'] : '';

        $this->serv_rpc_client->update_room([
            'id'            => $id,
            'game_id'       => $game_id,
            'name'          => $request->input('name', ''),
            'cover_img'     => empty($cover_img) ? '' : display_img($cover_img),
            'video_url'     => $request->input('video_url', ''),
            'desc'          => $request->input('desc', ''),
            'sort'          => $request->input('sort', 0),
            'status'        => $request->input('status', mod_room::ENABLE),
        ]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("房间修改 {$id}", $this->module_id);

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
        //根据游戏代码获取游戏信息
        $row = $this->repo_game->get_game_by_code('PG');
        $game_id = $row ? $row['id'] : '';

        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_room->save([
            'do'        => get_action(),
            'id'        => $request->input('id'),
            'game_id'   => $game_id,
            'name'      => $request->input('name', ''),
            'cover_img' => $request->input('cover_img', ''),
            'video_url' => $request->input('video_url', ''),
            'desc'      => $request->input('desc', ''),
            'sort'      => $request->input('sort', 0),
            'status'    => $request->input('status', mod_room::ENABLE),
        ], $ret_data);

        if ($status > 0)
        {
            DB::commit(); //手動提交事务
        }
        else
        {
            DB::rollBack(); //手動回滚事务
        }
        return [$status, $ret_data];
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

        $status = $this->repo_room->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_room->get_err_msg($status), $status);
        }
        //更新缓存
        //$this->repo_room->cache(true);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("房间刪除 {$id}", $this->module_id);

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
        $status = $this->repo_room->change_status([
            'id'        => $id,
            'status'    => mod_room::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_room->get_err_msg($status), $status);
        }
        //更新缓存
        //$this->repo_room->cache(true);
        //通知游戏服, 调用建房服务
        $row = $this->repo_room->find([
            'where' => [['id', '=', implode(",", $id)]],
        ]);
        $row = empty($row) ? []:$row->toArray();
        if($row)
        {
            $this->serv_rpc_client->update_room([
                'id'            => $row['id'],
                'game_id'       => $row['game_id'],
                'name'          => $row['name'],
                'cover_img'     => empty($row['cover_img']) ? '' : display_img($row['cover_img']),
                'video_url'     => $row['video_url'],
                'desc'          => $row['desc'],
                'sort'          => $row['sort'],
                'status'        => $row['status'],
            ]);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("房间启用 ".implode(",", $id), $this->module_id);

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
        $status = $this->repo_room->change_status([
            'id'        => $id,
            'status'    => mod_room::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_room->get_err_msg($status), $status);
        }
        //更新缓存
        //$this->repo_room->cache(true);
        //通知游戏服
        $row = $this->repo_room->find([
            'where' => [['id', '=', implode(",", $id)]],
        ]);
        $row = empty($row) ? []:$row->toArray();
        if($row)
        {
            $this->serv_rpc_client->update_room([
                'id'            => $row['id'],
                'game_id'       => $row['game_id'],
                'name'          => $row['name'],
                'cover_img'     => empty($row['cover_img']) ? '' : display_img($row['cover_img']),
                'video_url'     => $row['video_url'],
                'desc'          => $row['desc'],
                'sort'          => $row['sort'],
                'status'        => $row['status'],
            ]);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("房间禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
