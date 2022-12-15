<?php

namespace App\Http\Controllers\api;

use App\repositories\repo_agent;
use App\traits\trait_ctl_common;
use Illuminate\Http\Request;

/**
 * 公共接口控制器
 * Class ctl_common
 * @package App\Http\Controllers\api
 */
class ctl_common extends Controller
{
    use trait_ctl_common;

    private $repo_agent;

    public function __construct(repo_agent $repo_agent)
    {
        parent::__construct();
        $this->repo_agent = $repo_agent;
    }

    /**
     * 測試連線, 测试与服务器端连接是否正常
     * @param Request $request
     * @return mixed
     */
    public function check(Request $request)
    {
        $username = $request->input('agent_id', '');
        if(empty($username))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //获取代理信息
        $agent = $this->repo_agent->get_agent_by_username($username);
        if(empty($agent))
        {
            return res_error('代理id不存在', -2);
        }

        $data = [
            'version'   => '1.0.0', //返回版本号
        ];
        return res_success($data);
    }
}
