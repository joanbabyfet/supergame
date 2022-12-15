<?php

namespace App\Http\Controllers\socket;

use App\repositories\repo_admin_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * 处理运营后台相关业务, 避免将所有业务写在单一文件里
 * Class ctl_admin
 * @package App\Http\Controllers\socket
 */
class ctl_admin extends Controller
{
    private $repo_admin_user;

    public function __construct(
        repo_admin_user $repo_admin_user
    )
    {
        parent::__construct();
        $this->repo_admin_user              = $repo_admin_user;
    }

    /**
     * 根据token获取认证用户uid
     * @param $token
     * @return string|void
     */
    public function get_uid_by_token($token)
    {
        $key    = sprintf($this->repo_admin_user->token_key, $token);
        $uid    = Redis::get($key);
        return empty($uid) ? '' : $uid;
    }
}
