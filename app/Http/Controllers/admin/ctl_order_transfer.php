<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_order_transfer;
use App\repositories\repo_order_transfer;
use App\repositories\repo_user;
use Illuminate\Http\Request;

/**
 * 余额修改记录控制器
 * Class ctl_order_transfer
 * @package App\Http\Controllers\admin
 */
class ctl_order_transfer extends Controller
{
    private $repo_order_transfer;
    private $repo_user;

    public function __construct(
        repo_order_transfer $repo_order_transfer,
        repo_user $repo_user
    )
    {
        parent::__construct();
        $this->repo_order_transfer = $repo_order_transfer;
        $this->repo_user           = $repo_user;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $username   = $request->input('username', '');
        $realname   = $request->input('realname', '');
        $type       = $request->input('type'); //类型 1=充值 2=提款
        $page_size  = $request->input('page_size', $this->repo_order_transfer->page_size);
        $page       = $request->input('page', 1);

        //获取用户id
        $uids = [];
        $user_conds = [];
        $username and $user_conds['username'] = $username;
        $realname and $user_conds['realname'] = $realname;
        if($user_conds)
        {
            $users = $this->repo_user->get_list($user_conds);
            $uids = sql_in($users, 'id');
        }

        $conds = [
            'origin'        => [mod_order_transfer::ORIGIN_ADMIN], //只捞后台下单
            'uid'           => $uids,
            'type'          => $type,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'append'        => ['create_time_text', 'type_text'], //扩充字段
            'load'          => ['user_maps', 'create_user_maps', 'agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_order_transfer->get_list($conds);
        return res_success($rows);
    }
}
