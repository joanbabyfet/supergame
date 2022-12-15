<?php

namespace App\Http\Controllers\api;

use App\repositories\repo_agent;
use App\repositories\repo_app_key;
use Illuminate\Http\Request;

class ctl_agent extends Controller
{
    private $repo_agent;
    private $repo_app_key;

    public function __construct(
        repo_agent $repo_agent,
        repo_app_key $repo_app_key
    )
    {
        parent::__construct();
        $this->repo_agent = $repo_agent;
        $this->repo_app_key = $repo_app_key;
    }

    /**
     * 获取代理馀额
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_agent_balance(Request $request)
    {
        $app_id = $request->input('app_id', '');
        if(empty($app_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //通过应用id获取代理id
        $agent_id = $this->repo_app_key->get_field_value([
            'fields' => ['agent_id'],
            'where' => [
                ['app_id', '=', $app_id]
            ]
        ]);
        //获取代理信息
        $row = $this->repo_agent->find(['where' => [['id', '=', $agent_id]]]);

        $data = [
            'balance'   => money($row['remain_balance'], ''), //金额统一返回字符串
        ];
        return res_success($data);
    }
}
