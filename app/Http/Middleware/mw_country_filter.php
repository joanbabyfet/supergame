<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;

class mw_country_filter
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
        $ip = $request->ip();
        //$ip = '43.245.202.73'; //测试用
        $country = ip2country($ip);
        if (in_array($country, config('global.country_blacklist')) && !in_array($country, config('global.country_whitelist')))
        {
            //国家若误判,则通过添加ip白名单让其可访问
            if (in_array($ip, config('global.ip_whitelist')))
            {
                return $next($request);
            }
            //abort(403, "You are restricted to access the site"); //您被限制访问该网站
            return res_error('You are restricted to access the site', response::RESTRICT_ACCESS_SITE);
        }

        return $next($request);
    }
}
