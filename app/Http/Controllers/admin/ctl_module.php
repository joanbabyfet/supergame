<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_module;
use Illuminate\Http\Request;

class ctl_module extends Controller
{
    private $repo_module;
    private $repo_admin_user_oplog;

    public function __construct(
        repo_module $repo_module,
        repo_admin_user_oplog $repo_admin_user_oplog
    )
    {
        parent::__construct();
        $this->repo_module                = $repo_module;
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
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
        $status     = $request->input('status');
        $page_size  = $request->input('page_size', $this->repo_module->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'name'          => $name,
            'status'        => $status,
            'page_size'     => $page_size, //每页几条
            //'page'          => $page, //第几页
            'append'        => ['status_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $request->has('page') and $conds['page'] = $page; //第几页, 与下拉选项共用同接口

        $rows = $this->repo_module->get_list($conds);
        return res_success($rows);
    }
}
