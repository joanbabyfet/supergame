<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $api_namespace    = 'App\Http\Controllers\api';
    protected $admin_namespace  = 'App\Http\Controllers\admin';
    protected $adminag_namespace  = 'App\Http\Controllers\adminag';
    protected $client_namespace  = 'App\Http\Controllers\client';
    protected $web_namespace    = 'App\Http\Controllers\web';

    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/'; //通过guest中间件,检测该用户己认证时跳转到哪(默认/home)

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

//        $this->routes(function () {
//            Route::middleware('web')
//                ->group(base_path('routes/web.php'));
//
//            Route::prefix('api')
//                ->middleware('api')
//                ->group(base_path('routes/api.php'));
//        });

        parent::boot();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAdminRoutes();

        $this->mapAdminAgRoutes();

        $this->mapClientRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')        //默认用web中间件组
        //->prefix(config('global.web.domain'))    //二级目录 /example
        ->domain(config('global.web.domain'))  //子网域 example.local/example
        ->namespace($this->web_namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::middleware('api')          //api中间件组
        //->prefix(config('global.admin.domain'))    //二级目录 /adminapi/example
        ->domain(config('global.admin.domain'))  //子网域 adminapi.example.local/example
        ->namespace($this->admin_namespace)
            ->group(base_path('routes/admin.php'));
    }

    protected function mapAdminAgRoutes()
    {
        Route::middleware('api')          //api中间件组
        //->prefix(config('global.adminag.domain'))    //二级目录 /agapi/example
        ->domain(config('global.adminag.domain'))  //子网域 agapi.example.local/example
        ->namespace($this->adminag_namespace)
            ->group(base_path('routes/adminag.php'));
    }

    protected function mapClientRoutes()
    {
        Route::middleware('api')        //api中间件组
        //->prefix(config('global.client.domain'))    //二级目录 /clientapi/example
        ->domain(config('global.client.domain'))  //子网域 clientapi.example.local/example
        ->namespace($this->client_namespace)
            ->group(base_path('routes/client.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')        //api中间件组
        //->prefix(config('global.api.domain'))    //二级目录 /api/example
        ->domain(config('global.api.domain'))  //子网域 api.example.local/example
        ->namespace($this->api_namespace)
            ->group(base_path('routes/api.php'));
    }
}
