<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;

class mw_safe_ips
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
        //获取用户ip白名单
        $safe_ips = auth($guard)->user()->safe_ips;
        $arr_safe_ips = empty($safe_ips) ? [] : explode(',', $safe_ips);
        //登陆IP不在白名单，禁止操作
        if(!empty($safe_ips) && !in_array($request->ip(), $arr_safe_ips))
        {
            return res_error(trans('api.api_not_in_safe_ip'), response::NOT_IN_SAFE_IP);
        }
        return $next($request);
    }
}
