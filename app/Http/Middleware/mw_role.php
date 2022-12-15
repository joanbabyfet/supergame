<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class mw_role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  $role 格式 [1,2,3]
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$role)
    {
        //api接口使用,暫不指定守衛api
        if (user_has_role($role)) {
            return $next($request);
        }
        //throw UnauthorizedException::forRoles([$role]);
        return res_error(trans('api.api_no_permission'), response::NO_ROLE);
    }
}
