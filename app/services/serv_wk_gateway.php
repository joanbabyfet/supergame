<?php


namespace App\services;


use App\traits\trait_service_base;
use Illuminate\Support\Facades\Redis;

class serv_wk_gateway
{
    use trait_service_base;

    private $uid_clientid_key   = 'socket:uid_clientid:%s'; //uid -> clientid 映射
    private $clientid_uid_key   = 'socket:clientid_uid:%s'; //clientid -> uid 映射

    /**
     * 绑定uid
     * @param $client_id
     * @param $uid
     */
    public function bind_uid($client_id, $uid)
    {
        //缓存一天
        $key = sprintf($this->uid_clientid_key, $uid);
        Redis::hset($key, $client_id, time());
        Redis::expire($key, 24 * 3600); //哈希设置过期时间

        $map_key = sprintf($this->clientid_uid_key, $client_id);
        Redis::setex($map_key, 24 * 3600, $uid);
    }

    /**
     * 解绑uid
     * @param $client_id
     * @param $uid
     */
    public function unbind_uid($client_id, $uid)
    {
        //删除缓存
        $key = sprintf($this->uid_clientid_key, $uid);
        Redis::hdel($key, $client_id);

        $map_key = sprintf($this->clientid_uid_key, $client_id);
        Redis::del($map_key);
    }

    /**
     * 根据uid获取client_id
     * @param $uid
     * @return array
     */
    public function get_clientid_by_uid($uid)
    {
        $client_ids = [];
        //先从缓存里面找
        $key  = sprintf($this->uid_clientid_key, $uid);
        $data = Redis::hgetall($key);

        if (empty($data))
        {

        }
        else
        {
            $client_ids = array_keys($data);
        }
        return $client_ids;
    }

    /**
     * 删除clientid
     * @param $client_id
     */
    public function del_clientid($client_id)
    {
        $map_key = sprintf($this->clientid_uid_key, $client_id);
        $uid     = Redis::get($map_key); //获取用户id

        if (!empty($uid))
        {
            $this->unbind_uid($client_id, $uid);
        }
    }
}
