<?php

namespace App\Http\Controllers\api;

use App\repositories\repo_config;
use Illuminate\Http\Request;

class ctl_index extends Controller
{
    private $repo_config;

    public function __construct(
        repo_config $repo_config
    )
    {
        parent::__construct();
        $this->repo_config  = $repo_config;
    }

    /**
     * 获取首页数据, app启动页调用基础数据
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_index_data(Request $request)
    {

    }
}
