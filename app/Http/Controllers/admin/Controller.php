<?php

namespace App\Http\Controllers\admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 父控制器
 * Class Controller
 * @package App\Http\Controllers\admin
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $guard = ''; //当前使用守卫

    public function __construct()
    {
        if (!defined('IN_ADMIN')) define('IN_ADMIN', 1);
        $this->guard = config('global.admin.guard'); //admin守卫

        if(auth($this->guard)->check()) //确认当前用户是否通过认证
        {
            $this->uid = auth($this->guard)->user()->getAuthIdentifier();
            $this->user = auth($this->guard)->user()->toArray(); //获取token中user信息

            if (!defined('AUTH_UID'))  //当前认证uid常量,在model里也可使用
            {
                define('AUTH_UID', $this->uid);
            }
        }
    }
}
