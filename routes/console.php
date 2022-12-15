<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Artisan::command('app:install', function () {
    //要运行指令
    Artisan::call("key:generate");
    Artisan::call("migrate");
    Artisan::call("db:seed");

    $date = date('Y-m-d H:i:s');
    $this->info("[{$date}] install done"); //ANSI颜色 line=白 info=绿 error=红 comment=黄
})->purpose('安装应用');
