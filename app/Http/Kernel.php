<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [ //全局中间件，最先调用
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class, //解决跨域
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class, //检测是否系统维护中
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [ //定义中间件组
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1', //访问频率限制,允許每1分钟同一个用户同一个接口可以访问60次,超過會報錯
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\mw_country_filter::class, //国家过滤器
            //\App\Http\Middleware\mw_check_sign::class, //参数无token且有应用id, 检测参数签名
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [ //中间件别名设置，允许你使用别名调用中间件
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'safe_ips' => \App\Http\Middleware\mw_safe_ips::class, //用戶登陆IP限制
        'jwt_auth' => \App\Http\Middleware\mw_jwt_auth::class, //jwt认证中间件
        'assign_guard' => \App\Http\Middleware\mw_assign_guard::class, //指定当前请求使用哪种守卫
        'last_actived_time' => \App\Http\Middleware\mw_last_actived_time::class, //记录用户请求时的最后活跃时间
        'set_locale' => \App\Http\Middleware\mw_set_locale::class, //设置当前使用语言
        'check_sign' => \App\Http\Middleware\mw_check_sign::class, //检测参数签名
        'permission' => \App\Http\Middleware\mw_permission::class, //检测权限
        'role' => \App\Http\Middleware\mw_role::class, //检测角色
        'ip_filter' => \App\Http\Middleware\mw_ip_filter::class, //ip过滤器
        'country_filter' => \App\Http\Middleware\mw_country_filter::class, //国家过滤器
    ];
}
