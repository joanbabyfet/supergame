<?php


namespace App\services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Class serv_redis
 * @package App\services
    // 遇锁立刻返回
    if (!$this->serv_redis->lock('test'))
    {
        show_error();
        return;
    }
    do_job();
    $this->serv_redis->unlock('test');

    // 遇锁等待3秒
    if ($this->serv_redis->lock('test', 3))
    {
        do_job();
        $this->serv_redis->unlock('test');
    }
 */
class serv_redis
{
    //类型
    public $cache_type_map = [
        'admin_menu'     => '运营后台菜单',
        'agent_menu'     => '代理后台菜单',
        'permission'     => '角色和权限',
        'sys_db_config'  => '系统配置'
    ];

    /**
     * 清除运营后台菜单缓存
     * @return bool
     */
    public function clear_admin_menu()
    {
        $cache_key = 'admin_menu';
        Redis::del($cache_key);

        return true;
    }

    /**
     * 清除代理后台菜单缓存
     * @return bool
     */
    public function clear_agent_menu()
    {
        $cache_key = 'agent_menu';
        Redis::del($cache_key);

        return true;
    }

    /**
     * 清除角色和权限缓存
     * @return bool
     */
    public function clear_permission()
    {
        $cache_key = 'spatie.permission.cache';
        //app()['cache']->forget($cache_key);
        Cache::forget($cache_key);

        return true;
    }

    /**
     * 清除系统配置缓存
     * @return bool
     */
    public function clear_sys_db_config()
    {
        $cache_key = 'sys_db_config';
        Redis::del($cache_key);

        return true;
    }

    /**
     * 上锁，redis分布式锁，同时只能有一个人可以操作某个行为
     * @param string $name 锁的标识名
     * @param int $timeout 循环获取锁的等待超时时间，在此时间内会一直尝试获取锁直到超时，为0表示失败后直接返回不等待
     * @param int $expire 当前锁的最大生存时间(秒)，必须大于0，如果超过生存时间锁仍未被释放，则系统会自动强制释放
     * @param int $wait_interval_us 获取锁失败后挂起再试的时间间隔(微秒)
     * @return mixed
     */
    public function lock($name, $timeout = 0, $expire = 15, $wait_interval_us = 100000)
    {
        if ($name == null) return false;

        //取得当前时间
        $now = time();
        //获取锁失败时的等待超时时刻
        $timeout_at = $now + $timeout;
        //锁的最大生存时刻
        $expire_at = $now + $expire;
        $key = "lock:{$name}";

        while (true)
        {
            $result = Redis::set($key, $expire_at, "ex", $expire, "nx");//该锁有效时间默认15秒
            if ($result)
            {
                return true;
            }

            //循环请求锁，如果没设置锁失败的等待时间 或者 已超过最大等待时间了，那就退出
            if ($timeout <= 0 || $timeout_at < microtime(true))
            {
                break;
            }

            //隔 $wait_interval_us 0.1秒后继续 请求
            usleep($wait_interval_us);
        }

        return false;
    }

    /**
     * 解锁
     * @param $value
     * @return mixed
     */
    public function unlock($name)
    {
        //先判断是否存在此锁
        if ($this->is_locking($name))
        {
            //删除锁
            if (Redis::del("lock:{$name}"))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断当前是否拥有指定名字的锁
     * @param $name
     * @return mixed
     */
    public function is_locking($name)
    {
        //从redis返回该锁的生存时间
        return Redis::get("lock:{$name}");
    }
}
