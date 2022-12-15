<?php

namespace App\Http\Controllers\adminag;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 父控制器
 * Class Controller
 * @package App\Http\Controllers\adminag
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $guard = ''; //当前使用守卫
    protected $uid = '';
    protected $pid = '';
    protected $is_father = false;
    protected $user = [];

    public function __construct()
    {
        if (!defined('IN_ADMINAG')) define('IN_ADMINAG', 1);
        $this->guard = config('global.adminag.guard'); //agent守卫

        if(auth($this->guard)->check()) //确认当前用户是否通过认证
        {
            $this->uid = auth($this->guard)->user()->getAuthIdentifier();
            $this->is_father = empty(auth($this->guard)->user()->pid) ? true : false; //是否为主帐号
            $this->pid = $this->is_father ? $this->uid : auth($this->guard)->user()->pid; //有父渠道id用父渠道id
            $this->user = auth($this->guard)->user()->toArray(); //获取token中user信息

            if (!defined('AUTH_UID'))  //当前认证uid常量,在model里也可使用
            {
                define('AUTH_UID', $this->uid);
            }
        }
    }
}
