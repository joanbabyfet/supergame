<?php

namespace App\Http\Middleware;

use App\services\serv_req;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class mw_set_locale
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
        if (!empty(auth()->user()) && !empty(auth()->user()->language)) //优先使用登录用户当前语言环境
        {
            $lang = auth()->user()->language;
        }
        else
        {
            $lang = app(serv_req::class)->get_language();
        }
        app()->setLocale($lang); //设置语言

        return $next($request);
    }
}
