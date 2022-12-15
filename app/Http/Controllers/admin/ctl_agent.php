<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_agent;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_agent;
use App\repositories\repo_app_key;
use App\services\serv_rpc_client;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\JWTAuth;

class ctl_agent extends Controller
{
    private $repo_agent;
    private $repo_admin_user_oplog;
    private $repo_app_key;
    private $serv_util;
    private $serv_rpc_client;
    private $module_id;

    public function __construct(
        repo_agent $repo_agent,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_app_key $repo_app_key,
        serv_util $serv_util,
        serv_rpc_client $serv_rpc_client
    )
    {
        parent::__construct();
        $this->repo_agent               = $repo_agent;
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
        $this->repo_app_key             = $repo_app_key;
        $this->serv_util                = $serv_util;
        $this->serv_rpc_client          = $serv_rpc_client;
        $this->module_id = 2;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $date_start = $request->input('date_start'); //创建开始时间
        $date_end   = $request->input('date_end'); //创建结束时间
        $realname  = $request->input('realname', '');
        $username  = $request->input('username', '');
        $page_size  = get_action() == 'export' ? 100 :
            $request->input('page_size', $this->repo_agent->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'pid'           => '0', //只捞主帐号
            'realname'      => $realname,
            'username'      => $username,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'append'        => ['create_time_text'], //扩充字段
            'load'          => ['create_user_maps', 'app_key_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $request->has('page') and $conds['page'] = $page; //第几页, 与下拉选项共用同接口

        $rows = $this->repo_agent->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size); //总页数

        if(get_action() == 'export')
        {
            $titles = [ //与fields字段匹配后为导出栏目
                'realname'                  => '渠道名称',
                'username'                  => '渠道id',
                'desc'                      => '渠道说明',
                'create_time_text'          => '创建日期',
                'create_user_maps.realname' => '创建人',
                'agent_balance'             => '总额度',
                'remain_balance'            => '剩馀额度',
            ];

            $status = $this->serv_util->export_data([
                'page_no'       => $page,
                'rows'          => $rows['lists'],
                'file'          => $request->input('file', ''),
                'fields'        => $request->input('fields', []), //列表要导出字段
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
        $row = $this->repo_agent->find(['where' => [
            ['id', '=', $id],
            ['pid', '=', '0']
        ]]);
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
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("代理添加 ", $this->module_id);

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
        $id         = $request->input('id');
        $status = $this->save($request);
        if($status < 0)
        {
            $msg = ($status == -4) ? '该私钥已经存在' : $this->repo_agent->get_err_msg($status);
            return res_error($msg, $status);
        }
        //寫入日志
        $this->repo_admin_user_oplog->add_log("代理修改 {$id}", $this->module_id);

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
        $id      = $request->input('id', '');
        $app_key = $request->input('app_key', '');

        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_agent->save([
            'do'            => get_action(),
            'id'            => $id,
            'pid'           => '0',
            'username'      => $request->input('username'),
            'password'      => $request->input('password'),
            'realname'      => $request->input('realname', ''),
            'role_id'       => $request->input('role_id', config('global.role_general_agent')),
            'desc'          => $request->input('desc', ''),
            'agent_balance' => $request->input('agent_balance', 0),
            'currency'      => $request->input('currency', config('global.currency')),
            'wallet_type'   => $request->input('wallet_type', config('global.wallet_type')),
            'status'        => $request->input('status', mod_agent::ENABLE),
        ], $ret_data);

        if(get_action() == 'add')
        {
            //生成私钥
            $key_data = $this->repo_app_key->create_app_key();
            $app_id = $key_data['app_id'];
            $app_key = $key_data['app_key'];
            $agent_id = $ret_data['id'] ?? '';
        }
        elseif(get_action() == 'edit')
        {
            //通过代理id获取应用id
            $app_id = $this->repo_app_key->get_field_value([
                'fields' => ['app_id'],
                'where' => [
                    ['agent_id', '=', $id]
                ]
            ]);
            $agent_id = $id;

            //检测名称是否被使用
            $row = $this->repo_app_key->find(['where' => [
                ['app_key', '=', $app_key],
                ['app_id', '!=', $app_id],
            ]]);
            if($row)
            {
                $status = -4;
            }
        }

        //添加或更新数据
        $data[] = [
            'app_id'        => $app_id,
            'app_key'       => $app_key,
            'agent_id'      => $agent_id,
            'create_time'   => time(),
            'create_user'   => defined('AUTH_UID') ? AUTH_UID : '',
        ];

        $this->repo_app_key->insertOrUpdate($data,
            ['app_id'],
            ['app_key'],
        );

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
     * 删除 (暂不使用)
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

        $status = $this->repo_agent->del(['id' => $id]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //通知游戏服 DELETED = 0, DISABLED = 1, ENABLED = 2
        $this->serv_rpc_client->change_agent_status(['id' => $id, 'status' => 0]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("代理刪除 {$id}", $this->module_id);

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
        $status = $this->repo_agent->change_status([
            'id'        => $id,
            'status'    => mod_agent::ENABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //通知游戏服 DELETED = 0, DISABLED = 1, ENABLED = 2
        $this->serv_rpc_client->change_agent_status(['id' => implode(",", $id), 'status' => 2]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("代理启用 ".implode(",", $id), $this->module_id);

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
        $status = $this->repo_agent->change_status([
            'id'        => $id,
            'status'    => mod_agent::DISABLE,
        ]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //获取该代理與所有子帐号
        $sub_accounts = $this->repo_agent->lists(['where' => [
            ['id', '=', $id],
            ['pid', '=', $id, 'or'],
        ]])->toArray();
        $ids = array_column($sub_accounts, 'id');
        // 批量强制退出登录
        foreach ($ids as $v)
        {
            //干掉用户信息缓存
            $this->repo_agent->del_cache($v);
            //干掉token缓存
            $token = $this->repo_agent->get_token_by_uid($v);
            $this->repo_agent->unbind_token_uid($token, $v);
            //将该token放入黑名单
            $token and auth(config('global.adminag.guard'))->setToken($token)->invalidate();
        }
        //通知游戏服 DELETED = 0, DISABLED = 1, ENABLED = 2
        $this->serv_rpc_client->change_agent_status(['id' => implode(",", $id), 'status' => 1]);
        //寫入日志
        $this->repo_admin_user_oplog->add_log("代理禁用 ".implode(",", $id), $this->module_id);

        return res_success([], trans('api.api_disable_success'));
    }
}
