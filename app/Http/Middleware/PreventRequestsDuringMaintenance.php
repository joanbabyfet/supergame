<?php

namespace App\Http\Middleware;

use Closure;
use App\lib\response;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        $domain = $request->getHost(); //获取调用域名 例 adminapi.wwin.city

        //维护中只允许运营后台调用
        if($this->app->isDownForMaintenance() && !in_array($domain, [config('global.admin.domain')]))
        {
            return res_error(trans('api.api_in_maintenance'), response::IN_MAINTENANCE);
        }
        return $next($request);
    }
}
