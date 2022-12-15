<?php


namespace App\services;

use App\Models\mod_order_transfer;
use App\repositories\repo_agent;
use App\repositories\repo_order_transfer;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * 转帐记录
 * Class serv_order_transfer
 * @package App\services
 */
class serv_order_transfer
{
    use trait_service_base;

    private $serv_util;
    private $serv_wallet;
    private $repo_order_transfer;
    private $repo_user;
    private $repo_agent;
    private $serv_rpc_client;

    public function __construct(
        serv_util $serv_util,
        serv_wallet $serv_wallet,
        repo_order_transfer $repo_order_transfer,
        repo_user $repo_user,
        repo_agent $repo_agent,
        serv_rpc_client $serv_rpc_client
    )
    {
        $this->serv_util            = $serv_util;
        $this->serv_wallet          = $serv_wallet;
        $this->repo_order_transfer  = $repo_order_transfer;
        $this->repo_user            = $repo_user;
        $this->repo_agent           = $repo_agent;
        $this->serv_rpc_client      = $serv_rpc_client;
    }

    /**
     * 创建订单流程
     * @param array $data
     * @return int
     */
    public function create(array $data, &$ret_data = [])
    {
        $type = $data['type'] ?? null;
        $origin = $data['origin'] ?? null; //订单来源
        $remark = $data['remark'] ?? null; //后台备注

        $status = 1;
        try
        {
            //检测类型是否为1=充值或2=提现
            if(!array_key_exists($type, mod_order_transfer::$type_map))
            {
                $this->exception('类型不存在', -1);
            }

            //获取用户信息
            $user = $this->repo_user->find(['where' => [
                ['id', '=', $data['uid']]
            ]]);
            if(empty($user))
            {
                $this->exception('玩家不存在', -2);
            }

            //获取代理信息
            $agent = $this->repo_agent->find(['where' => [
                ['id', '=', $data['agent_id']]
            ]]);
            if(empty($agent))
            {
                $this->exception('代理不存在', -3);
            }

            if($agent['remain_balance'] < $data['amount'])
            {
                $this->exception('渠道额度已满', -4);
            }

            if($data['amount'] < config('global.min_amount'))
            {
                $this->exception('金额不能小于最低限额', -5);
            }

            //检测用户钱包余额是否足够
            $balance = $this->serv_wallet->get_balance($data['uid']);
            if ($type == mod_order_transfer::WITHDRAW)
            {
                if(empty($balance))
                {
                    $this->exception('馀额不足', -6);
                }

                //馀额少于提款金额返回-1, 否则返回1, 相等返回0, scale比较小数点2位
                if(bccomp($balance, $data['amount'], 2) === -1)
                {
                    $this->exception('馀额少于提款金额', -7);
                }
            }

            //写入订单信息
//            $status = $this->repo_order_transfer->create($data, $ret_data);
//            if($status < 0)
//            {
//                $msg = $this->repo_order_transfer->get_err_msg($status);
//                $status == -1 and $status = -8;
//                $status == -2 and $status = -9;
//                //$this->exception('创建订单失败', -8);
//                $this->exception($msg, $status);
//            }
//            $trade_no = $ret_data['id']; //系统交易单号

            //充值
//            if($type == mod_order_transfer::DEPOSIT)
//            {
//                $status = $this->serv_wallet->deposit([
//                    'uid'       => $data['uid'],
//                    'amount'    => $data['amount'],
//                    'currency'  => $data['currency'],
//                    'order_id'  => $trade_no,
//                ]);
//                if($status < 0)
//                {
//                    $this->exception('充值失败', -10);
//                }
//            }
            //提款
//            elseif($type == mod_order_transfer::WITHDRAW)
//            {
//                $status = $this->serv_wallet->withdraw([
//                    'uid'       => $data['uid'],
//                    'amount'    => $data['amount'],
//                    'currency'  => $data['currency'],
//                    'order_id'  => $trade_no,
//                ]);
//                if($status < 0)
//                {
//                    $this->exception('提款失败', -11);
//                }
//
//                //获取某代理提款金额
//                $withdraw_amount = $this->repo_order_transfer->get_agent_withdraw_amount($data['agent_id']);
//                //更新代理剩馀额度, 每次提款时都更新代理剩馀额度字段
//                $remain_balance = ($agent['agent_balance'] > $withdraw_amount) ? $agent['agent_balance'] - $withdraw_amount : 0;
//                $this->repo_agent->update([
//                    'remain_balance'    => $remain_balance,
//                ], ['id' => $data['agent_id']]);
//            }

            //玩家钱包馀额写入redis
//            $amount = money($user->balance, '');
//            $cache_key = sprintf($this->repo_user->wallet_cache_key, $data['uid']);
//            Redis::set($cache_key, $amount);

            //充值
            if($type == mod_order_transfer::DEPOSIT)
            {
                $this->serv_rpc_client->deposit([
                    'origin'            => $origin,
                    'holder_id'         => $data['uid'],
                    'name'              => 'Default Wallet',
                    'slug'              => 'default',
                    'description'       => '',
                    'balance'           => $data['amount'],
                    'transaction_id'    => $data['transaction_id'], //平台送订单号
                    'currency'          => $data['currency'],
                    'remark'            => $remark,
                    'agent_id'          => $data['agent_id'],
                ], $ret);
            }
            //提款
            elseif($type == mod_order_transfer::WITHDRAW)
            {
                $this->serv_rpc_client->withdraw([
                    'origin'            => $origin,
                    'holder_id'         => $data['uid'],
                    'balance'           => $data['amount'],
                    'transaction_id'    => $data['transaction_id'], //平台送订单号
                    'currency'          => $data['currency'],
                    'remark'            => $remark,
                    'agent_id'          => $data['agent_id'],
                ], $ret);

                //获取某代理提款金额
                $withdraw_amount = $this->repo_order_transfer->get_agent_withdraw_amount($data['agent_id']);
                //更新代理剩馀额度, 每次提款时都更新代理剩馀额度字段
                $remain_balance = ($agent['agent_balance'] > $withdraw_amount) ? $agent['agent_balance'] - $withdraw_amount : 0;
                $this->repo_agent->update([
                    'remain_balance'    => $remain_balance,
                ], ['id' => $data['agent_id']]);
            }

            //返回数据
            $ret_data = [
                'amount'            => money($data['amount'], ''),
                'currency'          => $data['currency'],
                'trade_no'          => $ret['order_id'] ?? '', //系统生成的交易单号
            ];
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }
}
