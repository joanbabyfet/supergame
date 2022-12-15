<?php

namespace App\Http\Middleware;

use App\repositories\repo_user;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class mw_last_actived_time
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check())  //用户已认证
        {
            $expire_time = 5 * 60; //单位:秒
            Redis::setex('user_online_' . Auth::user()->id, $expire_time, true);

//            $guard = get_default_guard();
//            if(in_array($guard, [config('global.api.guard')]))
//            {
//                //游戏api请求时会更新用户最后活跃时间, 在5分钟内即在线
//                app(repo_user::class)->update([
//                    'last_actived_time' => time(),
//                ], ['id' => Auth::user()->id]);
//            }
        }
        return $next($request);
    }
}
