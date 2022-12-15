<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_transaction;
use Illuminate\Http\Request;

class ctl_wallet_log extends Controller
{
    private $repo_transaction;
    private $repo_admin_user_oplog;

    public function __construct(
        repo_transaction $repo_transaction,
        repo_admin_user_oplog $repo_admin_user_oplog
    )
    {
        parent::__construct();
        $this->repo_transaction          = $repo_transaction;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $type       = $request->input('type');
        $uid        = $request->input('uid');
        $user_type  = $request->input('user_type');
        $page_size  = $request->input('page_size', $this->repo_transaction->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'type'      => $type,
            'uid'       => $uid,
            'user_type' => $user_type,
            'page_size' => $page_size, //每页几条
            'page'      => $page, //第几页
            'count'     => 1, //是否返回总条数
        ];
        $rows = $this->repo_transaction->get_list($conds);
        return res_success($rows);
    }
}
