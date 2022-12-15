<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;

class mw_permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        //当前路由别名 例 admin.example.index
        $permission = Route::currentRouteName();

        //获取该用户权限
        $purviews = get_purviews([
            'guard' => $guard,
            'field' => 'name'
        ]);

        //有超级管理员权限*可访问所有地址
        if (user_can($permission, $guard) || in_array('*', $purviews)) {
            return $next($request); //如果没有停止则向后执行
        }
        //throw UnauthorizedException::forPermissions([$permission]);
        return res_error(trans('api.api_no_permission'), response::NO_PERMISSION);
    }
}
