<?php

namespace App\Http\Controllers\admin;

use App\repositories\repo_admin_user_oplog;
use App\services\serv_redis;
use Illuminate\Http\Request;

/**
 * 缓存管理控制器
 * Class ctl_cache
 * @package App\Http\Controllers\admin
 */
class ctl_cache extends Controller
{
    private $repo_admin_user_oplog;
    private $serv_redis;
    private $module_id;

    public function __construct(
        repo_admin_user_oplog $repo_admin_user_oplog,
        serv_redis $serv_redis
    )
    {
        parent::__construct();
        $this->repo_admin_user_oplog    = $repo_admin_user_oplog;
        $this->serv_redis    = $serv_redis;
        $this->module_id = 23;
    }

    /**
     * 清除缓存
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function clear(Request $request)
    {
        $type = $request->input('type', ''); //类型
        if(empty($type))
        {
            return res_error(trans('api.api_param_error'), -1);
        }
        if(!method_exists(serv_redis::class, "clear_{$type}"))
        {
            return res_error('类型不存在', -2);
        }

        //调用服务方法干掉緩存
        $method = "clear_{$type}";
        $this->serv_redis->$method();
        //寫入日志
        $this->repo_admin_user_oplog->add_log("清除".$this->serv_redis->cache_type_map[$type]."缓存", $this->module_id);

        return res_success([], trans('api.api_clear_success'));
    }
}
