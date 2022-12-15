<?php

namespace App\Http\Controllers\api;

use App\repositories\repo_agent;
use App\repositories\repo_app_key;
use App\repositories\repo_order_transfer;
use Illuminate\Http\Request;

class ctl_order_transfer extends Controller
{
    private $repo_agent;
    private $repo_app_key;
    private $repo_order_transfer;

    public function __construct(
        repo_agent $repo_agent,
        repo_app_key $repo_app_key,
        repo_order_transfer $repo_order_transfer
    )
    {
        parent::__construct();
        $this->repo_agent = $repo_agent;
        $this->repo_app_key = $repo_app_key;
        $this->repo_order_transfer = $repo_order_transfer;
    }

    /**
     * 查询订单, 查询玩家上下分订单信息，通过 status 状态来判断上下分是否成功
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function check_order(Request $request)
    {
        $app_id         = $request->input('app_id', '');
        $transaction_id = $request->input('transaction_id', '');
        if(empty($app_id) || empty($transaction_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //获取订单详情
        $row = $this->repo_order_transfer->find(['where' => [['transaction_id', '=', $transaction_id]]]);
        $row = empty($row) ? []:$row->toArray();
        $amount = empty($row) ? 0 : $row['amount'];

        $status = 1;
        if(empty($row)) { //不存在
            $status = -1;
        }
        elseif($row['pay_status'] == -1) { //失败
            $status = -2;
        }
        elseif($row['pay_status'] == 0) { //处理中
            $status = -3;
        }

        $data = [
            'status'    => $status, //状态 -1=不存在 -2=失败 -3=处理中 1=成功
            'amount'    => money($amount, ''), //金额统一返回字符串
        ];
        return res_success($data);
    }
}
