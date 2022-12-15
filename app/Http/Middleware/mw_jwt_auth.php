<?php

namespace App\Http\Middleware;

use App\lib\response;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * Class mw_jwt_auth
 * 这里要继承jwt 的 BaseMiddleware
 * jwt包括header头部, payload负载(用户信息), signature签名
 * @package App\Http\Middleware
 */
class mw_jwt_auth extends BaseMiddleware
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
        if (!$token = $this->auth->setRequest($request)->getToken())
        {
            return res_error('未带token', response::NO_TOKEN);
        }

        try
        {
            //加多一道客户端信息验证, 防止为造
//            if ($payload = $this->auth->parseToken()->getPayload())
//            {
//                if ($payload->get('ipa') != md5($request->ip())) {
//                    return res_error(trans('api.api_origin_ip_invalid'), response::ORIGIN_IP_INVALID);
//                }
//
//                if ($payload->get('ura') != md5($request->userAgent())) {
//                    return res_error(trans('api.api_origin_user_agent_invalid'), response::ORIGIN_USER_AGENT_INVALID);
//                }
//
//                if ($payload->get('hst') != md5(gethostname())) {
//                    return res_error(trans('api.api_origin_hostname_invalid'), response::ORIGIN_HOSTNAME_INVALID);
//                }
//            }

            //根据令牌里用户id查数据表, 返回用户信息
            if (!$user = $this->auth->parseToken()->authenticate())
            {
                return res_error('未登录或登录超时', response::TOKEN_AUTH_FAIL);
            }
            return $next($request);
        }
        catch (TokenExpiredException $e)
        {
            try
            {
                $token = $this->auth->refresh();

                //使用一次性登录以保证此次请求的成功
                $id = $this->auth->manager()->getPayloadFactory()
                    ->buildClaimsCollection()->toPlainArray()['sub']; //sub字段为用户id
                auth($guard)->onceUsingId($id); //保证该次请求成功

                //后置中间件, 响应前执行任务
                return $this->setAuthenticationHeader($next($request), $token)
                    ->header('Access-Control-Expose-Headers', 'Authorization'); //在响应头中返回新的 token
            }
            catch (JWTException $e)
            {
                return res_error('token过期, 请重新登录', response::TOKEN_EXPIRED); //存取令牌过期
            }
        }
        catch (TokenInvalidException $e) //登出调用logout后将令牌放入黑名单, 使它无效
        {
            return res_error('token无效, 请重新登录', response::TOKEN_INVALID);
        }
        catch (JWTException $e) //捕获到刷新令牌过期, 用户无法刷新令牌, 需重新登录
        {
            return res_error('刷新token过期, 请重新登录', response::REFRESH_TOKEN_EXPIRED);
        }
    }
}
