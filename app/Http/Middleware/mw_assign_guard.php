<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class mw_assign_guard
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
        if($guard != null)
        {
            auth()->shouldUse($guard); //指定当前请求使用哪种守卫
        }
        return $next($request);
    }

    public function terminate($request, $response)
    {
        // 这里是响应后调用方法
    }
}
