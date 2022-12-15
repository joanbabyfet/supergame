<?php

namespace App\Http\Controllers\client;

use App\Models\mod_marquee;
use App\repositories\repo_marquee;
use Illuminate\Http\Request;

class ctl_marquee extends Controller
{
    private $repo_marquee;
    private $page_size;

    public function __construct(
        repo_marquee $repo_marquee
    )
    {
        parent::__construct();
        $this->repo_marquee          = $repo_marquee;
        $this->page_size             = 10;
    }

    /**
     * 获取列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        //$page_size  = $request->input('page_size', $this->repo_marquee->page_size);
        $page       = $request->input('page', 1);

        $conds = [
            'status'    => mod_marquee::ENABLE,
            'page_size' => $this->page_size, //每页几条, 先写死防止被送1000条以上
            'page'      => $page, //第几页
            'append'    => ['status_text', 'create_time_text'], //扩充字段
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_marquee->get_list($conds);
        return res_success($rows);
    }
}
