<?php

namespace App\Http\Controllers\socket;

use App\repositories\repo_agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * 处理代理后台相关业务, 避免将所有业务写在单一文件里
 * Class ctl_agent
 * @package App\Http\Controllers\socket
 */
class ctl_agent extends Controller
{
    private $repo_agent;

    public function __construct(
        repo_agent $repo_agent
    )
    {
        parent::__construct();
        $this->repo_agent              = $repo_agent;
    }

    /**
     * 根据token获取认证用户uid
     * @param $token
     * @return string|void
     */
    protected function get_uid_by_token($token)
    {
        $key    = sprintf($this->repo_agent->token_key, $token);
        $uid    = Redis::get($key);
        return empty($uid) ? '' : $uid;
    }
}
