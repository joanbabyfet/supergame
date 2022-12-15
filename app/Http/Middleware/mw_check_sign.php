<?php

namespace App\Http\Middleware;

use App\repositories\repo_app_key;
use Closure;
use Illuminate\Http\Request;

/**
 * 给第三方应用对接用
 * Class mw_check_sign
 * @package App\Http\Middleware
 */
class mw_check_sign
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
        $app_id     = $request->input('app_id'); //应用id
        $sign       = $request->input('sign'); //签名字段
        $sign_data  = $request->all(); //获取所有参数

        //某些来源允许不登录访问特定接口，但是需要验证接口签名, 验证第三方访问, 例平台
        if (empty($app_id) || empty($sign)) //无效请求
        {
            return res_error(trans('api.api_invalid_request'), -1);
        }

        //根据应用id获取代理私钥信息
        $app = app(repo_app_key::class)->find([
            'fields' => ['app_key'],
            'where' => [
                ['app_id', '=', $app_id]
            ]
        ]);
        $app = empty($app) ? []:$app->toArray();
        if (empty($app))
        {
            return res_error(trans('api.api_invalid_request'), -2);
        }

        //调试状态下，不检测参数签名
        config('app.debug') and $sign = sign($sign_data, $app['app_key']);  //测试用
        if (!check_sign($sign_data, $app['app_key'], $sign)) //检查签名
        {
            return res_error(trans('api.api_sign_error'), -3);
        }

        //验证必填参数
        //$params = ['uid', 'version', 'timezone', 'device', 'language', 'os'];
//        $params = ['version', 'language'];
//        foreach ($params as $f)
//        {
//            if (!$request->headers->has($f))
//            {
//                return res_error(trans('api.api_param_error'), -4);
//            }
//        }
        return $next($request);
    }
}
