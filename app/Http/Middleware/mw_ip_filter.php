<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;
use MaxMind\Exception\InvalidRequestException;

class mw_ip_filter
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
        // 如果IP在黑名单里面，而且不在白名单里面
        $ip = $request->ip();
        if ( in_array($ip, config('global.ip_blacklist')) && !in_array($ip, config('global.ip_whitelist')) )
        {
            //abort(403, "You are restricted to access the site"); //您被限制访问该网站
            return res_error('You are restricted to access the site', response::RESTRICT_ACCESS_SITE);
        }

        return $next($request);
    }
}
