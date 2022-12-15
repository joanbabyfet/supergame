<?php


namespace App\services;


use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\Redis;

class serv_wallet
{
    use trait_service_base;

    private $repo_user;

    public function __construct(repo_user $repo_user)
    {
        $this->repo_user    = $repo_user;
    }

    /**
     * 充值/上分 (暂不使用改由rpc写入)
     * @param array $data
     * @return int|mixed
     */
    public function deposit(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'uid'               => 'required',
            'amount'            => 'required',
            'order_id'          => '', //订单id
            'currency'          => '', //币种
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $uid = $data_filter['uid'];
            $amount = $data_filter['amount'];
            $order_id = $data_filter['order_id'] ?? '';
            $currency = $data_filter['currency'] ?? '';
            $meta = []; //保存其他信息方便追查
            $order_id and $meta['order_id'] = $order_id;
            $currency and $meta['currency'] = $currency;

            $user = $this->repo_user->find(['where' => [['id', '=', $uid]]]);
            $user->deposit($amount, $meta);
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

    /**
     * 提款/下分 (暂不使用改由rpc写入)
     * @return int|mixed
     */
    public function withdraw(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'uid'               => 'required',
            'amount'            => 'required', //正数, 不能给负数
            'order_id'          => '', //订单id
            'currency'          => '', //币种
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $uid = $data_filter['uid'];
            $amount = $data_filter['amount'];
            $order_id = $data_filter['order_id'] ?? '';
            $currency = $data_filter['currency'] ?? '';
            $meta = []; //保存其他信息方便追查
            $order_id and $meta['order_id'] = $order_id;
            $currency and $meta['currency'] = $currency;

            $user = $this->repo_user->find(['where' => [['id', '=', $uid]]]);
            $user->withdraw($amount, $meta);
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

    /**
     * 根据用户id获取钱包馀额
     * @param $username
     * @return array|mixed
     */
    public function get_balance($uid)
    {
        $balance = 0;
        if (empty($uid)) return $balance;

        //获取钱包馀额从redis
        $cache_key = sprintf($this->repo_user->wallet_cache_key, $uid);
        $balance = Redis::get($cache_key);
        $balance = empty($balance) ? 0 : $balance;

        $user = $this->repo_user->find(['where' => [['id', '=', $uid]]]);
        //以redis钱包馀额为基准, 因数据库钱包馀额可能因延迟不能实时同步, 造成馀额不一致
        $balance = ($balance != $user->balanceInt) ? $balance : $user->balanceInt;
        return $balance; //返回int类型
    }
}
